<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Support\Str;

/**
 * Request global para validación de búsquedas, paginación y filtros.
 * 
 * Úsalo en los métodos index() de tus controladores para asegurar
 * que los parámetros de ordenamiento, paginación y rangos sean seguros.
 */
class SearchRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string|max:50',
            'sort_order' => 'sometimes|in:asc,desc,ASC,DESC',
            'filters' => 'sometimes|array',
        ];

        // Validar dinámicamente cualquier filtro de rango detectado
        foreach ($this->all() as $key => $value) {
            // Ignoramos valores vacíos
            if ($value === null || $value === '') {
                continue;
            }

            // 1. Validar Rangos de Fechas (_start / _end)
            if (Str::endsWith($key, '_start')) {
                $rules[$key] = 'date';
            }
            if (Str::endsWith($key, '_end')) {
                $rules[$key] = 'date';

                // Validación de consistencia: Fecha Fin >= Fecha Inicio
                $prefix = substr($key, 0, -4);
                if ($this->has($prefix . '_start')) {
                    $rules[$key] .= '|after_or_equal:' . $prefix . '_start';
                }
            }

            // 2. Validar Rangos Numéricos (_min / _max)
            if (Str::endsWith($key, '_min')) {
                $rules[$key] = 'numeric';
            }
            if (Str::endsWith($key, '_max')) {
                $rules[$key] = 'numeric';

                // Validación de consistencia: Máximo >= Mínimo
                $prefix = substr($key, 0, -4);
                if ($this->has($prefix . '_min')) {
                    $rules[$key] .= '|gte:' . $prefix . '_min';
                }
            }
        }

        return $rules;
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'El número de página debe ser un entero.',
            'page.min' => 'El número de página debe ser al menos 1.',
            'per_page.max' => 'La cantidad de registros por página no puede exceder 100.',
            'sort_order.in' => 'El orden debe ser asc o desc.',
            '*.date' => 'El campo debe ser una fecha válida.',
            '*.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
            '*.numeric' => 'El campo debe ser un número.',
            '*.gte' => 'El valor máximo debe ser mayor o igual al valor mínimo.',
        ];
    }
}
