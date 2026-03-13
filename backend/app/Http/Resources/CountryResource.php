<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'iso_code_2'      => $this->iso_code_2,
            'iso_code_3'      => $this->iso_code_3,
            'currency_code'   => $this->currency_code,
            'currency_symbol' => $this->currency_symbol,
            'symbol_left'     => $this->symbol_left,
            'decimal_place'   => $this->decimal_place,
            'decimal_point'   => $this->decimal_point,
            'thousands_point' => $this->thousands_point,
        ];
    }
}
