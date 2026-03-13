<?php

namespace App\Domain\Checkout\Services;

use App\Domain\Checkout\Models\Basket;
use App\Domain\Checkout\Models\BasketItem;
use App\Domain\Checkout\Models\CheckoutSession;
use App\Domain\Member\Models\Member;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Organisation\Models\Organisation;
use App\Domain\Payment\Models\OrderPayment;
use App\Domain\Payment\Models\Transaction;
use App\Domain\Subscription\Models\MemberSubscription;
use App\Domain\Subscription\Models\SubscriptionPriceOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private readonly PricingCalculator $pricing) {}

    // ── Basket ────────────────────────────────────────────────────────────────

    public function createBasket(Organisation $organisation, ?Member $member = null, string $type = 'member'): Basket
    {
        return Basket::create([
            'organisation_id' => $organisation->id,
            'member_id'       => $member?->id,
            'type'            => $type,
            'expires_at'      => now()->addHours(2),
        ]);
    }

    public function addItem(Basket $basket, string $subscriptionPriceOptionId, int $quantity = 1): BasketItem
    {
        $option = SubscriptionPriceOption::findOrFail($subscriptionPriceOptionId);

        abort_if(
            $option->subscription->organisation_id !== $basket->organisation_id,
            422,
            'Subscription does not belong to this organisation.'
        );

        abort_if(
            $option->published === 'unpublished',
            422,
            'This subscription option is not available.'
        );

        $item = $basket->items()->updateOrCreate(
            ['subscription_price_option_id' => $option->id],
            [
                'subscription_id' => $option->subscription_id,
                'quantity'        => $quantity,
            ]
        );

        return $item->fresh(['subscription', 'priceOption']);
    }

    public function removeItem(Basket $basket, BasketItem $item): void
    {
        abort_unless($item->basket_id === $basket->id, 404);
        $item->delete();
    }

    // ── Checkout session ─────────────────────────────────────────────────────

    public function startCheckout(Basket $basket, string $mode = 'member'): CheckoutSession
    {
        abort_if($basket->isEmpty(), 422, 'Basket is empty.');
        abort_if($basket->isExpired(), 422, 'Basket has expired.');

        $session = $basket->checkoutSession;

        if ($session && $session->isComplete()) {
            abort(422, 'Checkout already completed.');
        }

        if (! $session) {
            $session = CheckoutSession::create([
                'basket_id'       => $basket->id,
                'organisation_id' => $basket->organisation_id,
                'step'            => 'init',
                'mode'            => $mode,
                'allocations'     => [],
                'forms'           => [],
                'area_status'     => [],
            ]);
        }

        return $session;
    }

    public function captureEmail(CheckoutSession $session, string $email): CheckoutSession
    {
        abort_unless($session->mode === 'public', 422, 'Email capture is only for public checkouts.');
        $session->update([
            'initiator_email' => $email,
            'step'            => 'allocations',
        ]);

        return $session->fresh();
    }

    /**
     * Store which member (or group) each basket item is being purchased for.
     *
     * $allocations = [
     *   ['basket_item_id' => '...', 'member_id' => '...'],
     *   ['basket_item_id' => '...', 'group_id'  => '...'],
     * ]
     */
    public function setAllocations(CheckoutSession $session, array $allocations): CheckoutSession
    {
        $basketItemIds = $session->basket->items()->pluck('id')->toArray();

        foreach ($allocations as $allocation) {
            abort_unless(
                in_array($allocation['basket_item_id'], $basketItemIds),
                422,
                'Allocation references an item not in this basket.'
            );
        }

        $session->update([
            'allocations' => $allocations,
            'step'        => 'membership_form',
        ]);
        $session->markStepComplete('allocations');

        return $session->fresh();
    }

    /**
     * Merge form data (purchaser details, subscription forms, etc.) into the session.
     */
    public function setFormData(CheckoutSession $session, array $forms): CheckoutSession
    {
        $existing = $session->forms ?? [];
        $session->update([
            'forms' => array_merge($existing, $forms),
            'step'  => 'payment',
        ]);
        $session->markStepComplete('membership_form');

        return $session->fresh();
    }

    /**
     * Store the chosen payment method for the checkout.
     */
    public function setPaymentMethod(CheckoutSession $session, string $paymentMethodId): CheckoutSession
    {
        $forms = $session->forms ?? [];
        $forms['payment_method_id'] = $paymentMethodId;

        $session->update([
            'forms' => $forms,
            'step'  => 'review',
        ]);
        $session->markStepComplete('payment');

        return $session->fresh();
    }

    // ── Order creation ────────────────────────────────────────────────────────

    /**
     * Calculate pricing and persist an Order from the basket contents.
     */
    public function createOrder(CheckoutSession $session): Order
    {
        abort_unless(in_array($session->step, ['review', 'payment']), 422, 'Checkout is not at the review step.');

        $basket = $session->basket->loadMissing('items.priceOption.subscription', 'member');
        $totals = $this->pricing->basketTotals($basket);

        return DB::transaction(function () use ($session, $basket, $totals) {
            $member = $basket->member;

            $order = Order::create([
                'organisation_id'  => $basket->organisation_id,
                'member_id'        => $member?->id,
                'name'             => $member?->full_name ?? ($session->initiator_email ?? 'Guest'),
                'email'            => $member?->email ?? $session->initiator_email ?? '',
                'payment_method_id' => $session->forms['payment_method_id'] ?? null,
                'status'           => 'pending',
                'date_placed'      => today(),
                'currency_code'    => $totals['currency'],
                'tax_total'        => $totals['tax'],
                'total'            => $totals['total'],
            ]);

            foreach ($basket->items as $index => $item) {
                $line = $totals['lines'][$index];

                OrderItem::create([
                    'order_id'    => $order->id,
                    'item_type'   => 'subscription',
                    'item_id'     => $item->subscription_id,
                    'description' => $item->subscription->name . ' — ' . $item->priceOption->name,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $line['unit_price'],
                    'tax_rate'    => 0,
                    'total'       => $line['total'],
                ]);
            }

            // Create an order payment record if a numeric amount is owed
            if ((float) $totals['total'] > 0 && ! empty($session->forms['payment_method_id'])) {
                $payment = OrderPayment::create([
                    'order_id'          => $order->id,
                    'organisation_id'   => $basket->organisation_id,
                    'member_id'         => $member?->id,
                    'payment_method_id' => $session->forms['payment_method_id'],
                    'amount_due'        => $totals['total'],
                    'currency_code'     => $totals['currency'],
                    'status'            => 'pending',
                ]);
            }

            // Link the order back to the session
            $session->update(['order_id' => $order->id]);

            return $order;
        });
    }

    // ── Finalise ──────────────────────────────────────────────────────────────

    /**
     * Complete the checkout:
     * 1. Mark order complete
     * 2. Create MemberSubscription records for each line item
     * 3. Clean up the basket
     */
    public function finalizeCheckout(CheckoutSession $session): Order
    {
        abort_unless($session->order_id, 422, 'No order has been created for this checkout.');
        abort_unless($session->step === 'review', 422, 'Checkout must be at the review step to finalize.');

        $order = Order::with('items')->findOrFail($session->order_id);
        abort_if($order->status === 'completed', 422, 'Order is already finalized.');

        $basket = $session->basket->loadMissing('items.priceOption.subscription');
        $allocations = collect($session->allocations ?? []);

        return DB::transaction(function () use ($session, $order, $basket, $allocations) {
            foreach ($basket->items as $item) {
                $allocation = $allocations->firstWhere('basket_item_id', $item->id);
                $memberId   = $allocation['member_id'] ?? $basket->member_id;

                if (! $memberId) {
                    continue; // Cannot create subscription without a member target
                }

                $priceOption = $item->priceOption;
                $subscription = $item->subscription;
                $startDate = today();

                MemberSubscription::create([
                    'member_id'                   => $memberId,
                    'organisation_id'             => $basket->organisation_id,
                    'subscription_id'             => $subscription->id,
                    'subscription_price_option_id' => $priceOption->id,
                    'payment_method_id'           => $session->forms['payment_method_id'] ?? null,
                    'order_id'                    => $order->id,
                    'starts_at'                   => $startDate,
                    'ends_at'                     => $this->calculateEndDate($startDate, $subscription),
                    'renewal_type'                => $subscription->renewal_type,
                    'status'                      => 'active',
                ]);
            }

            // Mark order complete
            $order->update([
                'status'        => 'completed',
                'date_finished' => today(),
            ]);

            // Ledger entry: debit member, credit organisation
            if ((float) $order->total > 0) {
                Transaction::create([
                    'organisation_id' => $order->organisation_id,
                    'member_id'       => $order->member_id,
                    'order_id'        => $order->id,
                    'description'     => 'Order #' . $order->id,
                    'currency_code'   => $order->currency_code,
                    'debit'           => $order->total,
                    'credit'          => 0,
                ]);
            }

            // Mark session complete and destroy basket
            $session->update(['step' => 'complete', 'payment_status' => 'paid']);
            $basket->items()->delete();
            $basket->delete();

            return $order->fresh();
        });
    }

    // ── State ─────────────────────────────────────────────────────────────────

    public function getState(Basket $basket): array
    {
        $session = $basket->checkoutSession;
        $basket->loadMissing('items.subscription', 'items.priceOption');
        $totals = $this->pricing->basketTotals($basket);

        return [
            'basket'  => $basket,
            'session' => $session,
            'totals'  => $totals,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function calculateEndDate(\Illuminate\Support\Carbon $start, \App\Domain\Subscription\Models\Subscription $subscription): ?\Illuminate\Support\Carbon
    {
        $period = $subscription->period;

        return match ($period) {
            'day'      => $start->copy()->addDay(),
            'week'     => $start->copy()->addWeek(),
            'month'    => $start->copy()->addMonth(),
            'year'     => $start->copy()->addYear(),
            'lifetime' => null,
            'none'     => null,
            default    => null,
        };
    }
}
