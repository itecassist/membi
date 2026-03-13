<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountingCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                               => $this->id,
            'organisation_config_financial_id' => $this->organisation_config_financial_id,
            'code'                             => $this->code,
            'description'                      => $this->description,
            'created_at'                       => $this->created_at,
            'updated_at'                       => $this->updated_at,
        ];
    }
}
