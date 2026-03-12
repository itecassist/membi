<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'organisation_id'  => $this->organisation_id,
            'name'             => $this->name,
            'description'      => $this->description,
            'membership_type'  => $this->membership_type,
            'period'           => $this->period,
            'renewal_type'     => $this->renewal_type,
            'pricing_type'     => $this->pricing_type,
            'published'        => $this->published,
            'is_joining_fee'   => $this->is_joining_fee,
            'price_options'    => SubscriptionPriceOptionResource::collection($this->whenLoaded('priceOptions')),
            'price_options_count' => $this->whenCounted('priceOptions'),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
