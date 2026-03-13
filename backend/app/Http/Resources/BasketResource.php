<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'organisation_id' => $this->organisation_id,
            'member_id'       => $this->member_id,
            'type'            => $this->type,
            'captured_email'  => $this->captured_email,
            'expires_at'      => $this->expires_at,
            'is_expired'      => $this->isExpired(),
            'items'           => BasketItemResource::collection($this->whenLoaded('items')),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
