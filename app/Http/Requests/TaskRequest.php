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
        // Common rules for creation and updating
        $rules = [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'string|in:pendiente,en_progreso,completada',
            'due_date' => 'nullable|date',
            'priority' => 'integer|min:1|max:5',
        ];

        // For POST requests (creation), the title is required
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
            'title.required' => 'The title is mandatory',
            'title.max' => 'The title cannot exceed 255 characters',
            'status.in' => 'The status must be pending, in_progress or completed',
            'priority.min' => 'The priority must be at least 1',
            'priority.max' => 'The priority cannot be greater than 5',
            'due_date.date' => 'The expiration date must be a valid date',
        ];
    }
}
