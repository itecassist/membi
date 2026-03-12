<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'membership_type' => ['required', 'in:individual,group'],
            'period'          => ['required', 'in:day,week,month,year,lifetime,none,instalments'],
            'renewal_type'    => ['required', 'in:auto_renew,manual,not_renewable'],
            'pricing_type'    => ['required', 'in:flat,family,corporate,tiered,custom_variable'],
            'published'       => ['required', 'in:published,renewal_only,unpublished'],
            'is_joining_fee'  => ['boolean'],
        ];
    }
}
