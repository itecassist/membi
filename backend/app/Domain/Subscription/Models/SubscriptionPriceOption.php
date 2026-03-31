<?php

namespace App\Domain\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPriceOption extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'subscription_id',
        'label',
        'eligibility',
        'pricing_type',
        'pricing_config',
        'price',
        'price_min',
        'price_max',
        'currency_code',
        'setup_price',
        'instance_type',
        'max_members',
        'use_pro_rata',
        'allow_instalments',
        'offer_trial',
        'rollover_period_days',
        'is_active',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'price'               => 'decimal:2',
            'price_min'           => 'decimal:2',
            'price_max'           => 'decimal:2',
            'setup_price'         => 'decimal:2',
            'pricing_config'      => 'array',
            'use_pro_rata'        => 'boolean',
            'allow_instalments'   => 'boolean',
            'offer_trial'         => 'boolean',
            'is_active'           => 'boolean',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function memberSubscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }

    public function newMemberSettings(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SubscriptionPriceOptionNewMember::class, 'subscription_price_option_id');
    }

    public function lateFees(): HasMany
    {
        return $this->hasMany(SubscriptionPriceLateFee::class, 'subscription_price_option_id');
    }
}
