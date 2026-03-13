<?php

namespace App\Domain\Checkout\Models;

use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Models\SubscriptionPriceOption;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasketItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'basket_id',
        'subscription_id',
        'subscription_price_option_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function priceOption(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPriceOption::class, 'subscription_price_option_id');
    }
}
