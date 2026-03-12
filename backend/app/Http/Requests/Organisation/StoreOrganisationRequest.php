<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'seo_name'           => ['required', 'string', 'max:64', 'unique:organisations,seo_name', 'regex:/^[a-z0-9\-]+$/'],
            'email'              => ['required', 'email'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'logo'               => ['nullable', 'string'],
            'website'            => ['nullable', 'url'],
            'description'        => ['nullable', 'string'],
            'timezone'           => ['nullable', 'timezone'],
            'free_trail'         => ['boolean'],
            'free_trail_end_date' => ['nullable', 'date'],
            'is_active'          => ['boolean'],
        ];
    }
}
