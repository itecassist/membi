<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationConfigFinancialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'organisation_id'    => $this->organisation_id,
            'currency'           => $this->currency,
            'vat_status'         => $this->vat_status,
            'vat_number'         => $this->vat_number,
            'financial_year_end' => $this->financial_year_end,
            'updated_at'         => $this->updated_at,
        ];
    }
}
