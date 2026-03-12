<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'organisation_id'  => $this->organisation_id,
            'name'             => $this->name,
            'type'             => $this->type,
            'email_contact_id' => $this->email_contact_id,
            'is_active'        => $this->is_active,
            'members_count'    => $this->whenCounted('members'),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
