<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPriceLateFeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                            => $this->id,
            'subscription_price_option_id'  => $this->subscription_price_option_id,
            'price'                         => $this->price,
            'renewal_date'                  => $this->renewal_date?->toDateString(),
            'late_fee'                      => $this->late_fee,
            'applies_from'                  => $this->applies_from?->toDateString(),
            'created_at'                    => $this->created_at,
            'updated_at'                    => $this->updated_at,
        ];
    }
}
