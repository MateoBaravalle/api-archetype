<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Common rules for all requests
        $rules = [
            'name' => 'string|min:3|max:255|regex:/^[\p{L}\s]+$/u',
            'email' => [
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
        ];

        // If it's a password update
        if ($this->isMethod('put') && $this->route()->getName() === 'users.password.update') {
            return [
                'current_password' => 'required|current_password',
                'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])[A-Za-z\d@$!%*?&.]+$/|confirmed',
            ];
        }

        // If it's a profile update
        if ($this->isMethod('put') && $this->route()->getName() === 'users.profile.update') {
            $rules['name'] = 'required|' . $rules['name'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name is mandatory.',
            'name.min' => 'The name must be at least 3 characters.',
            'name.max' => 'The name cannot have more than 255 characters.',
            'name.regex' => 'The name can only contain letters and spaces.',
            'email.email' => 'The email must be valid.',
            'email.max' => 'The email cannot have more than 255 characters.',
            'email.unique' => 'This email is already registered.',
            'current_password.required' => 'The current password is mandatory.',
            'current_password.current_password' => 'The current password is incorrect.',
            'password.required' => 'The new password is mandatory.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&.).',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
