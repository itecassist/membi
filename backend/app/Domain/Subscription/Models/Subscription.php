<?php

namespace App\Domain\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organisation_id',
        'name',
        'description',
        'virtual_form_id',
        'document_id',
        'membership_type',
        'period',
        'renewal_type',
        'pricing_type',
        'published',
        'is_joining_fee',
    ];

    protected function casts(): array
    {
        return [
            'is_joining_fee' => 'boolean',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Organisation\Models\Organisation::class);
    }

    public function priceOptions(): HasMany
    {
        return $this->hasMany(SubscriptionPriceOption::class);
    }

    public function memberSubscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }
}
