<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'type'             => ['required', 'in:family,corporate'],
            'email_contact_id' => ['nullable', 'uuid', 'exists:members,id'],
            'is_active'        => ['boolean'],
        ];
    }
}
