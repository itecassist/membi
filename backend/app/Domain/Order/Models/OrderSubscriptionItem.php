<?php

namespace App\Domain\Order\Models;

use App\Domain\Subscription\Models\MemberSubscription;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Models\SubscriptionPriceOption;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSubscriptionItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'subscription_id',
        'subscription_price_option_id',
        'member_subscription_id',
        'adult_quantity',
        'junior_quantity',
        'unit_price',
        'subtotal',
        'currency_code',
        'period_start',
        'period_end',
    ];

    protected function casts(): array
    {
        return [
            'adult_quantity'  => 'integer',
            'junior_quantity' => 'integer',
            'unit_price'      => 'decimal:2',
            'subtotal'        => 'decimal:2',
            'period_start'    => 'date',
            'period_end'      => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function priceOption(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPriceOption::class, 'subscription_price_option_id');
    }

    public function memberSubscription(): BelongsTo
    {
        return $this->belongsTo(MemberSubscription::class);
    }
}
