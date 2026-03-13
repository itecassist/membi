<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'member_id'         => $this->member_id,
            'organisation_id'   => $this->organisation_id,
            'name'              => $this->name,
            'email'             => $this->email,
            'payment_method_id' => $this->payment_method_id,
            'payment_reference' => $this->payment_reference,
            'status'            => $this->status,
            'date_placed'       => $this->date_placed,
            'date_finished'     => $this->date_finished,
            'comments'          => $this->comments,
            'currency_code'     => $this->currency_code,
            'tax_total'         => $this->tax_total,
            'total'             => $this->total,
            'items'             => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
