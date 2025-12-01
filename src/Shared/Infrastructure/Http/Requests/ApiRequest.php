<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
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
     * Sanitiza los datos de entrada antes de la validación.
     */
    public function prepareForValidation(): void
    {
        $this->sanitizeInput();
    }

    /**
     * Sanitiza los datos de entrada.
     */
    protected function sanitizeInput(): void
    {
        $input = $this->all();

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // Elimina espacios en blanco al inicio y final
                $value = trim($value);

                // Convierte caracteres especiales a entidades HTML
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

                // Elimina caracteres de control
                $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

                // Normaliza espacios múltiples a uno solo
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
            response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}


