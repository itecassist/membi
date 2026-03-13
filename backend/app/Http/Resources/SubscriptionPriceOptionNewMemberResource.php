<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPriceOptionNewMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                            => $this->id,
            'subscription_price_option_id'  => $this->subscription_price_option_id,
            'enable_rollover'               => $this->enable_rollover,
            'rollover_period_days'          => $this->rollover_period_days,
            'enable_pro_rata_pricing'       => $this->enable_pro_rata_pricing,
            'pro_rata_pricing'              => $this->pro_rata_pricing,
            'updated_at'                    => $this->updated_at,
        ];
    }
}
