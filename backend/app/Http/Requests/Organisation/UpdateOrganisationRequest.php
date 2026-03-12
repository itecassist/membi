<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        $organisationId = $this->route('organisation')?->id;

        return [
            'name'               => ['sometimes', 'string', 'max:255'],
            'seo_name'           => ['sometimes', 'string', 'max:64', Rule::unique('organisations', 'seo_name')->ignore($organisationId), 'regex:/^[a-z0-9\-]+$/'],
            'email'              => ['sometimes', 'email'],
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
