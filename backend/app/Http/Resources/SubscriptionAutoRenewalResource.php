<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionAutoRenewalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                              => $this->id,
            'subscription_id'                 => $this->subscription_id,
            'enable_auto_renewal'             => $this->enable_auto_renewal,
            'apply_to_all_subscription_fees'  => $this->apply_to_all_subscription_fees,
            'payment_method_id'               => $this->payment_method_id,
            'order_expiry_days'               => $this->order_expiry_days,
            'should_have_form'                => $this->should_have_form,
            'virtual_form_id'                 => $this->virtual_form_id,
            'message'                         => $this->message,
            'updated_at'                      => $this->updated_at,
        ];
    }
}
