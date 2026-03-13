<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberPaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'member_id'        => $this->member_id,
            'payment_method_id'=> $this->payment_method_id,
            'gateway_reference'=> $this->gateway_reference,
            'is_active'        => $this->is_active,
            'expires_at'       => $this->expires_at,
            'payment_method'   => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
