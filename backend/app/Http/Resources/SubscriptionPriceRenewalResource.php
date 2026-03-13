<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPriceRenewalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'subscription_id'      => $this->subscription_id,
            'schedule_late_fees'   => $this->schedule_late_fees,
            'late_fee_option'      => $this->late_fee_option,
            'late_fee_percentage'  => $this->late_fee_percentage,
            'renewal_day_month'    => $this->renewal_day_month,
            'updated_at'           => $this->updated_at,
        ];
    }
}
