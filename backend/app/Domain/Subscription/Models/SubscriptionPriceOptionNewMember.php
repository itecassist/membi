<?php

namespace App\Domain\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * New-member-specific settings for a subscription price option:
 * rollover and pro-rata pricing configuration.
 */
class SubscriptionPriceOptionNewMember extends Model
{
    use HasUuids;

    protected $fillable = [
        'subscription_price_option_id',
        'enable_rollover',
        'rollover_period_days',
        'enable_pro_rata_pricing',
        'pro_rata_pricing',
    ];

    protected function casts(): array
    {
        return [
            'enable_rollover'         => 'boolean',
            'enable_pro_rata_pricing' => 'boolean',
            'pro_rata_pricing'        => 'decimal:2',
        ];
    }

    public function priceOption(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPriceOption::class, 'subscription_price_option_id');
    }
}
