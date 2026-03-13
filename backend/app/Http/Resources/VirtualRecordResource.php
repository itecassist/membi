<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VirtualRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'organisation_id' => $this->organisation_id,
            'member_id'       => $this->member_id,
            'virtual_form_id' => $this->virtual_form_id,
            'data'            => $this->data,
            'form'            => new VirtualFormResource($this->whenLoaded('form')),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
