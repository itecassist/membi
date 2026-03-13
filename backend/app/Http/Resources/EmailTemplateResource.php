<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'organisation_id' => $this->organisation_id,
            'name'            => $this->name,
            'subject'         => $this->subject,
            'content'         => $this->content,
            'header'          => $this->header,
            'footer'          => $this->footer,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
