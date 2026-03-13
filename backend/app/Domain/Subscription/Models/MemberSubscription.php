<?php

namespace App\Domain\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberSubscription extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'member_id',
        'organisation_id',
        'subscription_id',
        'subscription_price_option_id',
        'payment_method_id',
        'order_id',
        'starts_at',
        'ends_at',
        'renewal_type',
        'status',
        'renewal_status',
        'payment_gateway_reference',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at'   => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Member\Models\Member::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Organisation\Models\Organisation::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function priceOption(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPriceOption::class, 'subscription_price_option_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Order\Models\Order::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Extend this subscription by one period after a successful renewal payment.
     * Creates a new MemberSubscription record for the next period and marks
     * this one as renewed.
     */
    public function extendForRenewal(?\App\Domain\Payment\Models\OrderPayment $orderPayment = null): self
    {
        $subscription = $this->subscription;
        $newStart     = ($this->ends_at ?? now())->copy()->addDay();

        $newEndsAt = match ($subscription->period) {
            'day'      => $newStart->copy()->addDay(),
            'week'     => $newStart->copy()->addWeek(),
            'month'    => $newStart->copy()->addMonth(),
            'year'     => $newStart->copy()->addYear(),
            'lifetime' => null,
            default    => null,
        };

        $renewal = static::create([
            'member_id'                   => $this->member_id,
            'organisation_id'             => $this->organisation_id,
            'subscription_id'             => $this->subscription_id,
            'subscription_price_option_id'=> $this->subscription_price_option_id,
            'payment_method_id'           => $this->payment_method_id,
            'order_id'                    => $orderPayment?->order_id,
            'starts_at'                   => $newStart,
            'ends_at'                     => $newEndsAt,
            'renewal_type'                => $this->renewal_type,
            'status'                      => 'active',
            'renewal_status'              => null,
        ]);

        // Mark the current period as renewed
        $this->update(['renewal_status' => 'renewed', 'status' => 'expired']);

        return $renewal;
    }
}
