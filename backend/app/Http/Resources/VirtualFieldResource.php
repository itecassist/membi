<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VirtualFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'virtual_form_id' => $this->virtual_form_id,
            'field_name'      => $this->field_name,
            'description'     => $this->description,
            'required'        => $this->required,
            'type'            => $this->type,
            'options'         => $this->options,
            'gdpr_sensitive'  => $this->gdpr_sensitive,
            'active'          => $this->active,
            'sort_order'      => $this->sort_order,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
