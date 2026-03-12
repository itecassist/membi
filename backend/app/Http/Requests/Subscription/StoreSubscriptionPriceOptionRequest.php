<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionPriceOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'eligibility'  => ['required', 'in:individual,adult,junior,family,corporate'],
            'pricing_type' => ['required', 'in:flat,tiered,custom_variable'],
            'price'        => ['required', 'numeric', 'min:0'],
            'price_min'    => ['nullable', 'numeric', 'min:0'],
            'price_max'    => ['nullable', 'numeric', 'min:0', 'gte:price_min'],
            'published'    => ['required', 'in:published,renewals_only,unpublished'],
        ];
    }
}
