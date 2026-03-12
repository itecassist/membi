<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['nullable', 'string', 'max:50'],
            'first_name'     => ['sometimes', 'string', 'max:50'],
            'last_name'      => ['sometimes', 'string', 'max:50'],
            'email'          => ['sometimes', 'email', 'max:255'],
            'mobile_phone'   => ['nullable', 'string', 'max:30'],
            'date_of_birth'  => ['nullable', 'date'],
            'gender'         => ['nullable', 'in:female,male,other,prefer_not_to_say'],
            'member_number'  => ['nullable', 'string', 'max:30'],
            'classification' => ['nullable', 'string', 'max:100'],
            'joined_at'      => ['nullable', 'date'],
            'is_active'      => ['boolean'],
        ];
    }
}
