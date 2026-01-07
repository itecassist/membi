<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationConfigSubscription extends Model
{
    use HasFactory;

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
}
