<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Payment\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Auto-renewal configuration for a subscription type.
 */
class SubscriptionAutoRenewal extends Model
{
    use HasUuids;

    protected $fillable = [
        'subscription_id',
        'enable_auto_renewal',
        'apply_to_all_subscription_fees',
        'payment_method_id',
        'order_expiry_days',
        'should_have_form',
        'virtual_form_id',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'enable_auto_renewal'             => 'boolean',
            'apply_to_all_subscription_fees'  => 'boolean',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
