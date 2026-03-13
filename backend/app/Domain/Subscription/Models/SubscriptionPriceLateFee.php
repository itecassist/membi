<?php

namespace App\Domain\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Late fee tiers applied per price option after a renewal date passes.
 */
class SubscriptionPriceLateFee extends Model
{
    use HasUuids;

    protected $fillable = [
        'subscription_price_option_id',
        'price',
        'renewal_date',
        'late_fee',
        'applies_from',
    ];

    protected function casts(): array
    {
        return [
            'price'        => 'decimal:2',
            'late_fee'     => 'decimal:2',
            'renewal_date' => 'date',
            'applies_from' => 'date',
        ];
    }

    public function priceOption(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPriceOption::class, 'subscription_price_option_id');
    }
}
