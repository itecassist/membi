<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'seo_name'           => $this->seo_name,
            'email'              => $this->email,
            'phone'              => $this->phone,
            'logo'               => $this->logo,
            'website'            => $this->website,
            'description'        => $this->description,
            'timezone'           => $this->timezone,
            'free_trail'         => $this->free_trail,
            'free_trail_end_date' => $this->free_trail_end_date?->toDateString(),
            'is_active'          => $this->is_active,
            'members_count'      => $this->whenCounted('members'),
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
