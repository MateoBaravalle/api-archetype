<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Traits\ApiResponseFormatter;

abstract class ApiRequest extends FormRequest
{
    use ApiResponseFormatter;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    abstract public function rules(): array;

    /**
     * Sanitizes input data before validation.
     */
    public function prepareForValidation(): void
    {
        $this->sanitizeInput();
    }

    /**
     * Sanitizes input data.
     */
    protected function sanitizeInput(): void
    {
        $input = $this->all();

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // 1. Removes whitespace at the beginning and end
                $value = trim($value);

                // 2. Removes control characters (avoids errors in logs and exports)
                $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

                // 3. Normalizes multiple spaces to a single one for visual clarity
                $value = preg_replace('/\s+/', ' ', $value);

                $input[$key] = $value;
            }
        }

        $this->replace($input);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->errorResponse('Validation error', 422, $validator->errors()->toArray())
        );
    }
}
