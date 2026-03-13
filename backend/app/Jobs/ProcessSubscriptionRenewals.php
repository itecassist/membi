<?php

namespace App\Jobs;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Payment\Models\MemberPaymentMethod;
use App\Domain\Payment\Models\OrderPayment;
use App\Domain\Payment\Services\GoCardlessService;
use App\Domain\Subscription\Models\MemberSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProcessSubscriptionRenewals — queued/scheduled job.
 *
 * Run daily via your scheduler:
 *   $schedule->job(new ProcessSubscriptionRenewals())->dailyAt('02:00');
 *
 * Finds all active auto-renewing subscriptions expiring within the
 * configured lead-time window, creates renewal orders, and initiates
 * GoCardless payments where a mandate is stored.
 */
class ProcessSubscriptionRenewals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Days before expiry to trigger renewal. Default: 8 (GoCardless settlement window). */
    private int $leadDays;

    public function __construct(int $leadDays = 8)
    {
        $this->leadDays = $leadDays;
    }

    public function handle(): void
    {
        $renewalDate = now()->addDays($this->leadDays)->toDateString();

        // Find active subscriptions due for renewal within the lead window
        // that haven't already had a renewal order placed
        $due = MemberSubscription::query()
            ->with(['member.paymentMethods.paymentMethod.gatewayConfig', 'subscription.priceOption', 'priceOption'])
            ->where('renewal_type', 'auto_renew')
            ->where('status', 'active')
            ->whereDate('ends_at', '<=', $renewalDate)
            ->whereNull('renewal_status')
            ->get();

        Log::info("SubscriptionRenewal: {$due->count()} due within {$this->leadDays} days.");

        foreach ($due as $memberSub) {
            try {
                $this->processRenewal($memberSub);
            } catch (\Throwable $e) {
                Log::error('SubscriptionRenewal failed', [
                    'member_subscription_id' => $memberSub->id,
                    'error'                  => $e->getMessage(),
                ]);
            }
        }
    }

    private function processRenewal(MemberSubscription $memberSub): void
    {
        $priceOption = $memberSub->priceOption;
        $member      = $memberSub->member;
        $organisation = $memberSub->organisation ?? $member->organisation;

        // Find an active DD mandate stored for this member
        $memberMethod = MemberPaymentMethod::where('member_id', $member->id)
            ->where('is_active', true)
            ->whereHas('paymentMethod', fn ($q) => $q->where('type', 'direct_debit'))
            ->first();

        DB::transaction(function () use ($memberSub, $priceOption, $member, $memberMethod) {
            // Create a renewal order
            $order = Order::create([
                'organisation_id'  => $memberSub->organisation_id,
                'member_id'        => $member->id,
                'name'             => $member->full_name ?? $member->email,
                'email'            => $member->email,
                'payment_method_id' => $memberMethod?->payment_method_id,
                'status'           => 'pending',
                'date_placed'      => today(),
                'currency_code'    => 'GBP',
                'tax_total'        => 0,
                'total'            => $priceOption->price,
            ]);

            OrderItem::create([
                'order_id'    => $order->id,
                'item_type'   => 'subscription',
                'item_id'     => $memberSub->subscription_id,
                'description' => 'Renewal: ' . $memberSub->subscription->name . ' — ' . $priceOption->name,
                'quantity'    => 1,
                'unit_price'  => $priceOption->price,
                'tax_rate'    => 0,
                'total'       => $priceOption->price,
            ]);

            $orderPayment = null;

            if ($memberMethod && (float) $priceOption->price > 0) {
                $orderPayment = OrderPayment::create([
                    'order_id'          => $order->id,
                    'organisation_id'   => $memberSub->organisation_id,
                    'member_id'         => $member->id,
                    'payment_method_id' => $memberMethod->payment_method_id,
                    'is_renewal'        => true,
                    'currency_code'     => 'GBP',
                    'amount_due'        => $priceOption->price,
                    'status'            => 'pending',
                ]);

                // Charge via GoCardless mandate
                $gatewayConfig = $memberMethod->paymentMethod->gatewayConfig;
                if ($gatewayConfig?->is_active) {
                    $driver = new GoCardlessService($gatewayConfig);
                    $gcPaymentId = $driver->chargeMandate(
                        $memberMethod,
                        (int) ($priceOption->price * 100),
                        'GBP',
                        'Renewal: ' . $memberSub->subscription->name,
                        $orderPayment->tracking_token,
                    );

                    $orderPayment->update([
                        'gateway_transaction_id' => $gcPaymentId,
                        'status'                 => 'processing',
                    ]);
                }
            }

            // Flag subscription as renewal in progress, record the order so
            // the webhook handler can find it when the payment is confirmed
            $memberSub->update([
                'renewal_status' => 'renewal_initiated',
                'order_id'       => $order->id,
            ]);

            Log::info('Renewal initiated', [
                'member_subscription_id' => $memberSub->id,
                'order_id'               => $order->id,
                'order_payment_id'       => $orderPayment?->id,
            ]);
        });
    }
}
