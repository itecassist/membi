<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'email'              => $this->email,
            'email_verified_at'  => $this->email_verified_at,
            'is_admin'           => $this->is_admin,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
