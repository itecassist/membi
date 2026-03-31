<?php

namespace App\Domain\Order\Models;

use App\Domain\Payment\Models\OrderPayment;
use App\Domain\Payment\Models\Transaction;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'member_id',
        'organisation_id',
        'basket_id',
        'name',
        'email',
        'payment_method_id',
        'payment_reference',
        'status',
        'is_subscription_order',
        'provisioning_status',
        'prev_order_id',
        'next_order_id',
        'date_placed',
        'date_finished',
        'comments',
        'currency_code',
        'tax_total',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'date_placed'            => 'date',
            'date_finished'          => 'date',
            'tax_total'              => 'decimal:2',
            'total'                  => 'decimal:2',
            'is_subscription_order'  => 'boolean',
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

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function subscriptionItems(): HasMany
    {
        return $this->hasMany(\App\Domain\Order\Models\OrderSubscriptionItem::class);
    }

    public function basket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Domain\Checkout\Models\Basket::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
