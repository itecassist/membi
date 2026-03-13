<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'addressable_id'   => $this->addressable_id,
            'addressable_type' => $this->addressable_type,
            'line_1'           => $this->line_1,
            'line_2'           => $this->line_2,
            'line_3'           => $this->line_3,
            'line_4'           => $this->line_4,
            'postcode'         => $this->postcode,
            'country_id'       => $this->country_id,
            'zone_id'          => $this->zone_id,
            'country'          => new CountryResource($this->whenLoaded('country')),
            'zone'             => new ZoneResource($this->whenLoaded('zone')),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
