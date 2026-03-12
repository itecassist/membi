<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->user_id,
            'organisation_id' => $this->organisation_id,
            'full_name'       => $this->full_name,
            'title'           => $this->title,
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'email'           => $this->email,
            'mobile_phone'    => $this->mobile_phone,
            'date_of_birth'   => $this->date_of_birth?->toDateString(),
            'gender'          => $this->gender,
            'member_number'   => $this->member_number,
            'classification'  => $this->classification,
            'joined_at'       => $this->joined_at?->toDateString(),
            'is_active'       => $this->is_active,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
