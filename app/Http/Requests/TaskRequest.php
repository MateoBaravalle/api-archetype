<?php

declare(strict_types=1);

namespace App\Http\Requests;

class TaskRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Reglas comunes para creación y actualización
        $rules = [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'string|in:pendiente,en_progreso,completada',
            'due_date' => 'nullable|date',
            'priority' => 'integer|min:1|max:5',
        ];

        // Para peticiones POST (creación), el título es requerido
        if ($this->isMethod('post')) {
            $rules['title'] = 'required|string|max:255';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio',
            'title.max' => 'El título no puede exceder los 255 caracteres',
            'status.in' => 'El estado debe ser pendiente, en_progreso o completada',
            'priority.min' => 'La prioridad debe ser al menos 1',
            'priority.max' => 'La prioridad no puede ser mayor que 5',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida',
        ];
    }
}
