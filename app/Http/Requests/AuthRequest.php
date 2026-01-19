<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;

class AuthRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])[A-Za-z\d@$!%*?&.]+$/',
        ];

        // If email does not exist, apply registration rules
        if (! User::where('email', $this->email)->exists()) {
            $rules = array_merge($rules, [
                'name' => 'required|string|min:3|max:255|regex:/^[\p{L}\s]+$/u',
                'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])[A-Za-z\d@$!%*?&.]+$/|confirmed',
            ]);
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
            'name.max' => 'The name cannot have more than 255 characters.',
            'name.regex' => 'The name can only contain letters and spaces.',
            'email.required' => 'The email is mandatory.',
            'email.email' => 'The email must be valid.',
            'email.max' => 'The email cannot have more than 255 characters.',
            'password.required' => 'The password is mandatory.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&.).',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
