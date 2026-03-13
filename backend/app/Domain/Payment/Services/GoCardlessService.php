<?php

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Contracts\PaymentDriverInterface;
use App\Domain\Payment\Models\MemberPaymentMethod;
use App\Domain\Payment\Models\OrderPayment;
use App\Domain\Payment\Models\PaymentGatewayConfig;
use GoCardlessPro\Client;
use GoCardlessPro\Environment;
use Illuminate\Support\Facades\Log;

class GoCardlessService implements PaymentDriverInterface
{
    private Client $client;

    public function __construct(private readonly PaymentGatewayConfig $config)
    {
        $credentials = $config->getDecryptedConfig();

        $this->client = new Client([
            'access_token' => $credentials['access_token'] ?? '',
            'environment'  => ($credentials['environment'] ?? 'sandbox') === 'live'
                ? Environment::LIVE
                : Environment::SANDBOX,
        ]);
    }

    /**
     * Create a GoCardless Billing Request and return the hosted flow URL.
     * The member is redirected here to authorise a Direct Debit mandate.
     */
    public function initiatePayment(OrderPayment $payment, array $options = []): ?string
    {
        $successUrl = $options['success_url'] ?? config('app.url') . '/checkout/complete';
        $exitUrl    = $options['exit_url']    ?? config('app.url') . '/checkout/payment';

        // Create billing request
        $billingRequest = $this->client->billingRequests()->create([
            'params' => [
                'mandate_request' => [
                    'currency'    => $payment->currency_code,
                    'constraints' => [],
                ],
                'payment_request' => [
                    'amount'      => (int) ($payment->amount_due * 100), // GoCardless uses pence
                    'currency'    => $payment->currency_code,
                    'description' => 'Order ' . $payment->order_id,
                ],
                'links' => [
                    'customer' => $payment->gateway_customer_id ?? null,
                ],
                'metadata' => [
                    'tracking_token' => $payment->tracking_token,
                    'order_id'       => $payment->order_id,
                ],
            ],
        ]);

        // Create billing request flow (hosted page)
        $flow = $this->client->billingRequestFlows()->create([
            'params' => [
                'redirect_uri'    => $successUrl,
                'exit_uri'        => $exitUrl,
                'links'           => [
                    'billing_request' => $billingRequest->id,
                ],
            ],
        ]);

        // Store gateway transaction ID for reconciliation
        $payment->update([
            'gateway_transaction_id' => $billingRequest->id,
            'status'                 => 'pending',
        ]);

        return $flow->authorisation_url;
    }

    /**
     * Process inbound GoCardless webhooks.
     * Verifies the webhook signature and updates OrderPayment status.
     */
    public function handleWebhook(string $payload, array $headers): void
    {
        $webhookSecret = $this->config->getDecryptedConfig()['webhook_secret'] ?? '';

        try {
            $events = \GoCardlessPro\Webhook::parse($payload, $headers['Webhook-Signature'] ?? '', $webhookSecret);
        } catch (\GoCardlessPro\Core\Exception\InvalidSignatureException $e) {
            Log::warning('GoCardless webhook signature invalid', ['error' => $e->getMessage()]);
            abort(498, 'Invalid webhook signature.');
        }

        foreach ($events as $event) {
            match ($event->resource_type) {
                'payments' => $this->handlePaymentEvent($event),
                'mandates' => $this->handleMandateEvent($event),
                default    => null,
            };
        }
    }

    public function cancelPayment(OrderPayment $payment): void
    {
        if (! $payment->gateway_transaction_id) {
            return;
        }

        try {
            $this->client->payments()->cancel($payment->gateway_transaction_id);
            $payment->update(['status' => 'cancelled']);
        } catch (\Throwable $e) {
            Log::error('GoCardless cancel failed', [
                'order_payment_id' => $payment->id,
                'error'            => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a payment against an existing mandate (for renewals).
     */
    public function chargeMandate(MemberPaymentMethod $memberMethod, int $amountPence, string $currency, string $description, string $trackingToken): string
    {
        $payment = $this->client->payments()->create([
            'params' => [
                'amount'      => $amountPence,
                'currency'    => $currency,
                'description' => $description,
                'metadata'    => ['tracking_token' => $trackingToken],
                'links'       => [
                    'mandate' => $memberMethod->gateway_reference,
                ],
            ],
        ]);

        return $payment->id;
    }

    // ── Private event handlers ────────────────────────────────────────────────

    private function handlePaymentEvent(object $event): void
    {
        $gcPaymentId = $event->links->payment ?? null;
        if (! $gcPaymentId) {
            return;
        }

        $orderPayment = OrderPayment::where('gateway_transaction_id', $gcPaymentId)->first();
        if (! $orderPayment) {
            Log::warning('GoCardless payment event: no matching order payment', ['gc_payment_id' => $gcPaymentId]);
            return;
        }

        $newStatus = match ($event->action) {
            'paid_out'  => 'processed',
            'failed'    => 'error',
            'cancelled' => 'cancelled',
            default     => null,
        };

        if ($newStatus) {
            $orderPayment->update(['status' => $newStatus]);

            if ($newStatus === 'processed') {
                $orderPayment->update(['amount_paid' => $orderPayment->amount_due]);

                // If this was a renewal payment, extend the member's subscription
                if ($orderPayment->is_renewal) {
                    $this->finalizeRenewal($orderPayment);
                }
            }
        }
    }

    private function finalizeRenewal(\App\Domain\Payment\Models\OrderPayment $orderPayment): void
    {
        // Find the MemberSubscription tied to this renewal order
        $memberSub = \App\Domain\Subscription\Models\MemberSubscription::where('order_id', $orderPayment->order_id)
            ->where('renewal_status', 'renewal_initiated')
            ->first();

        if (! $memberSub) {
            Log::warning('Renewal finalize: no MemberSubscription found for order', [
                'order_payment_id' => $orderPayment->id,
                'order_id'         => $orderPayment->order_id,
            ]);
            return;
        }

        $memberSub->load('subscription');
        $newSub = $memberSub->extendForRenewal($orderPayment);

        Log::info('Renewal finalised: new subscription period created', [
            'old_member_subscription_id' => $memberSub->id,
            'new_member_subscription_id' => $newSub->id,
        ]);
    }

    private function handleMandateEvent(object $event): void
    {
        $mandateId = $event->links->mandate ?? null;
        if (! $mandateId) {
            return;
        }

        $memberMethod = MemberPaymentMethod::where('gateway_reference', $mandateId)->first();
        if (! $memberMethod) {
            return;
        }

        if ($event->action === 'cancelled') {
            $memberMethod->update(['is_active' => false]);
        }
    }
}
