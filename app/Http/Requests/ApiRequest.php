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
                // 1. Elimina espacios en blanco al inicio y final
                $value = trim($value);

                // 2. Elimina caracteres de control (evita errores en logs y exportaciones)
                $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

                // 3. Normaliza espacios múltiples a uno solo para limpieza visual
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
            $this->errorResponse('Error de validación', 422, $validator->errors()->toArray())
        );
    }
}
