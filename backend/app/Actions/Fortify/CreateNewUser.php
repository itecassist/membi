<?php

namespace App\Actions\Fortify;

use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'title' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'mobile_phone' => ['required', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['required', 'string'],
            'google_id' => ['nullable', 'string'],
            'facebook_id' => ['nullable', 'string'],
            'twitter_id' => ['nullable', 'string'],
            'github_id' => ['nullable', 'string'],
            'linkedin_id' => ['nullable', 'string'],
            'is_admin' => ['boolean'],
            'is_active' => ['boolean'],
            'last_login_at' => ['datetime'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return User::create([
            'title' => $input['title'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'mobile_phone' => $input['mobile_phone'],
            'date_of_birth' => $input['date_of_birth'],
            'gender' => $input['gender'],
            'google_id' => $input['google_id'] ??'',
            'facebook_id' => $input['facebook_id']??'',
            'twitter_id' => $input['twitter_id']??'',
            'github_id' => $input['github_id']??'',
            'linkedin_id' => $input['linkedin_id']??'',
            'is_admin' => $input['is_admin'] ?? false,
            'is_active' => $input['is_active'] ?? true,
            'last_login_at' => now(),
        ]);
    }
}
