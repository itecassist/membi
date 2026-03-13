<?php

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Contracts\PaymentDriverInterface;
use App\Domain\Payment\Models\OrderPayment;
use App\Domain\Payment\Models\PaymentGatewayConfig;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * WorldPay Access (formerly Worldpay) payment driver.
 *
 * Docs: https://developer.worldpay.com/docs/access-worldpay
 * Flow: Create a checkout session → redirect member to hosted page →
 *       WorldPay posts webhook notification → reconcile via trackingToken.
 */
class WorldPayService implements PaymentDriverInterface
{
    private string $merchantEntityId;
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;
    private string $webhookSecret;

    public function __construct(private readonly PaymentGatewayConfig $config)
    {
        $credentials = $config->getDecryptedConfig();

        $this->merchantEntityId = $credentials['merchant_entity_id'] ?? '';
        $this->apiKey           = $credentials['api_key'] ?? '';
        $this->apiSecret        = $credentials['api_secret'] ?? '';
        $this->webhookSecret    = $credentials['webhook_secret'] ?? '';
        $this->baseUrl          = ($credentials['environment'] ?? 'sandbox') === 'live'
            ? 'https://access.worldpay.com'
            : 'https://try.access.worldpay.com';
    }

    /**
     * Create a WorldPay checkout session and return the hosted page URL.
     */
    public function initiatePayment(OrderPayment $payment, array $options = []): ?string
    {
        $successUrl = $options['success_url'] ?? config('app.url') . '/checkout/complete';
        $cancelUrl  = $options['cancel_url']  ?? config('app.url') . '/checkout/payment';

        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->post("{$this->baseUrl}/payments/authorizations/", [
                'transactionReference' => $payment->tracking_token,
                'merchant'             => ['entity' => $this->merchantEntityId],
                'instruction'          => [
                    'narrative' => ['line1' => 'Membix Order'],
                    'value'     => [
                        'currency' => $payment->currency_code,
                        'amount'   => (int) ($payment->amount_due * 100),
                    ],
                ],
                'resultUrls' => [
                    'successUrl'     => $successUrl . '?token=' . $payment->tracking_token,
                    'cancelUrl'      => $cancelUrl,
                    'failureUrl'     => $cancelUrl . '?error=1',
                    'errorUrl'       => $cancelUrl . '?error=1',
                ],
            ]);

        if (! $response->successful()) {
            Log::error('WorldPay initiate payment failed', [
                'order_payment_id' => $payment->id,
                'status'           => $response->status(),
                'body'             => $response->body(),
            ]);
            abort(502, 'Payment gateway error. Please try again.');
        }

        $data = $response->json();
        $redirectUrl = data_get($data, '_links.payments:hosted-checkout.href');

        if (! $redirectUrl) {
            abort(502, 'WorldPay did not return a hosted payment URL.');
        }

        $payment->update([
            'gateway_transaction_id' => data_get($data, 'paymentInstrument.id'),
            'status'                 => 'pending',
        ]);

        return $redirectUrl;
    }

    /**
     * Handle WorldPay webhook notifications.
     * Verifies HMAC signature and updates OrderPayment status.
     */
    public function handleWebhook(string $payload, array $headers): void
    {
        $this->verifyWebhookSignature($payload, $headers);

        $data = json_decode($payload, true);

        $trackingToken = data_get($data, 'transactionReference');
        $orderPayment = OrderPayment::where('tracking_token', $trackingToken)->first();

        if (! $orderPayment) {
            Log::warning('WorldPay webhook: no matching order payment', ['tracking_token' => $trackingToken]);
            return;
        }

        $outcome = data_get($data, 'outcome');

        $newStatus = match ($outcome) {
            'authorized', 'sentForSettlement' => 'processed',
            'declined', 'failed'              => 'error',
            'cancelled'                       => 'cancelled',
            default                           => null,
        };

        if ($newStatus) {
            $orderPayment->update(['status' => $newStatus]);

            if ($newStatus === 'processed') {
                $orderPayment->update(['amount_paid' => $orderPayment->amount_due]);
            }
        }
    }

    public function cancelPayment(OrderPayment $payment): void
    {
        if (! $payment->gateway_transaction_id) {
            return;
        }

        Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->delete("{$this->baseUrl}/payments/authorizations/{$payment->gateway_transaction_id}");

        $payment->update(['status' => 'cancelled']);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function verifyWebhookSignature(string $payload, array $headers): void
    {
        $signature = $headers['X-WorldPay-Signature'] ?? $headers['x-worldpay-signature'] ?? '';

        $expected = hash_hmac('sha256', $payload, $this->webhookSecret);

        if (! hash_equals($expected, $signature)) {
            Log::warning('WorldPay webhook signature mismatch.');
            abort(498, 'Invalid webhook signature.');
        }
    }
}
