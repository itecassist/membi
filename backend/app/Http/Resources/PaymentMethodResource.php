<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'organisation_id'           => $this->organisation_id,
            'payment_gateway_config_id' => $this->payment_gateway_config_id,
            'type'                      => $this->type,
            'class'                     => $this->class,
            'name'                      => $this->name,
            'explanation'               => $this->explanation,
            'checkout_text'             => $this->checkout_text,
            'success_text'              => $this->success_text,
            'is_active'                 => $this->is_active,
            'is_default'                => $this->is_default,
            'admin_only'                => $this->admin_only,
            'requires_confirmation'     => $this->requires_confirmation,
            'surcharge_percentage'      => $this->surcharge_percentage,
            'surcharge_fixed'           => $this->surcharge_fixed,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
        ];
    }
}
