<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPriceOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'subscription_id' => $this->subscription_id,
            'name'            => $this->name,
            'eligibility'     => $this->eligibility,
            'pricing_type'    => $this->pricing_type,
            'price'           => $this->price,
            'price_min'       => $this->price_min,
            'price_max'       => $this->price_max,
            'published'       => $this->published,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
