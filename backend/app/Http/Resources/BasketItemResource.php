<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                          => $this->id,
            'basket_id'                   => $this->basket_id,
            'subscription_id'             => $this->subscription_id,
            'subscription_price_option_id'=> $this->subscription_price_option_id,
            'quantity'                    => $this->quantity,
            'subscription'                => new SubscriptionResource($this->whenLoaded('subscription')),
            'price_option'                => new SubscriptionPriceOptionResource($this->whenLoaded('priceOption')),
            'created_at'                  => $this->created_at,
            'updated_at'                  => $this->updated_at,
        ];
    }
}
