<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'order_id'    => $this->order_id,
            'item_type'   => $this->item_type,
            'item_id'     => $this->item_id,
            'description' => $this->description,
            'quantity'    => $this->quantity,
            'unit_price'  => $this->unit_price,
            'tax_rate'    => $this->tax_rate,
            'total'       => $this->total,
        ];
    }
}
