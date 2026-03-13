<?php

namespace App\Domain\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Renewal schedule settings for a subscription type.
 */
class SubscriptionPriceRenewal extends Model
{
    use HasUuids;

    protected $fillable = [
        'subscription_id',
        'schedule_late_fees',
        'late_fee_option',
        'late_fee_percentage',
        'renewal_day_month',
    ];

    protected function casts(): array
    {
        return [
            'schedule_late_fees'  => 'boolean',
            'late_fee_percentage' => 'decimal:2',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
