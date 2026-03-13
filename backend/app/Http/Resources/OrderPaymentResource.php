<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'order_id'               => $this->order_id,
            'organisation_id'        => $this->organisation_id,
            'member_id'              => $this->member_id,
            'payment_method_id'      => $this->payment_method_id,
            'is_manual'              => $this->is_manual,
            'requires_confirmation'  => $this->requires_confirmation,
            'is_renewal'             => $this->is_renewal,
            'currency_code'          => $this->currency_code,
            'amount_due'             => $this->amount_due,
            'amount_paid'            => $this->amount_paid,
            'status'                 => $this->status,
            'due_date'               => $this->due_date,
            'tracking_token'         => $this->tracking_token,
            'gateway_transaction_id' => $this->gateway_transaction_id,
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
