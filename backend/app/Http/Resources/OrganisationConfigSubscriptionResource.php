<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationConfigSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                                               => $this->id,
            'organisation_id'                                  => $this->organisation_id,
            'can_member_have_more_than_one_subscription'       => $this->can_member_have_more_than_one_subscription,
            'can_have_subscription_without_membership'         => $this->can_have_subscription_without_membership,
            'recently_expired_annual_subscription_months'      => $this->recently_expired_annual_subscription_months,
            'recently_expired_monthly_subscription_days'       => $this->recently_expired_monthly_subscription_days,
            'recently_expired_other_period_days'               => $this->recently_expired_other_period_days,
            'renew_annual_subscription_months'                 => $this->renew_annual_subscription_months,
            'renew_monthly_subscription_days'                  => $this->renew_monthly_subscription_days,
            'renew_other_subscription_days'                    => $this->renew_other_subscription_days,
            'forced_joining_fee'                               => $this->forced_joining_fee,
            'subscription_joining_id'                          => $this->subscription_joining_id,
            'auto_renewal_order_days'                          => $this->auto_renewal_order_days,
            'updated_at'                                       => $this->updated_at,
        ];
    }
}
