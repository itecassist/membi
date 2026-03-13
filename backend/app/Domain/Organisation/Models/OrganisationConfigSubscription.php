<?php

namespace App\Domain\Organisation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationConfigSubscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'can_member_have_more_than_one_subscription',
        'can_have_subscription_without_membership',
        'recently_expired_annual_subscription_months',
        'recently_expired_monthly_subscription_days',
        'recently_expired_other_period_days',
        'renew_annual_subscription_months',
        'renew_monthly_subscription_days',
        'renew_other_subscription_days',
        'forced_joining_fee',
        'subscription_joining_id',
        'auto_renewal_order_days',
    ];

    protected function casts(): array
    {
        return [
            'can_member_have_more_than_one_subscription' => 'boolean',
            'can_have_subscription_without_membership'   => 'boolean',
            'forced_joining_fee'                         => 'boolean',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
