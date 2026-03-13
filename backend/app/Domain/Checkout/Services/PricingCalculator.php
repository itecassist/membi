<?php

namespace App\Domain\Checkout\Services;

use App\Domain\Checkout\Models\Basket;
use App\Domain\Checkout\Models\BasketItem;
use App\Domain\Subscription\Models\SubscriptionPriceOption;

class PricingCalculator
{
    /**
     * Calculate the line total for a single basket item.
     * For tiered/custom_variable pricing the caller should supply
     * an explicit unit price (e.g. from admin input); otherwise we
     * use the option's flat price.
     */
    public function lineTotal(BasketItem $item, ?float $unitPriceOverride = null): array
    {
        $option = $item->priceOption ?? $item->priceOption()->first();
        $unitPrice = $unitPriceOverride ?? (float) $option->price;
        $quantity = $item->quantity;
        $lineTotal = round($unitPrice * $quantity, 2);

        return [
            'unit_price' => $unitPrice,
            'quantity'   => $quantity,
            'total'      => $lineTotal,
        ];
    }

    /**
     * Calculate the totals for an entire basket.
     * Returns [ subtotal, tax, total, currency, lines[] ]
     *
     * VAT is not applied by default — tax is determined by the
     * organisation's financial config (to be implemented in Phase 2+).
     * For now we return 0 tax and note it must be applied later.
     */
    public function basketTotals(Basket $basket, float $vatRate = 0.0): array
    {
        $basket->loadMissing('items.priceOption');

        $lines = $basket->items->map(function (BasketItem $item) {
            return $this->lineTotal($item);
        });

        $subtotal = $lines->sum('total');
        $tax = round($subtotal * $vatRate, 2);
        $total = round($subtotal + $tax, 2);

        return [
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'total'    => $total,
            'currency' => 'GBP',
            'lines'    => $lines->values()->toArray(),
        ];
    }
}
