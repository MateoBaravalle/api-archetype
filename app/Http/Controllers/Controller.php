<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Traits\ApiResponseFormatter;
use App\Support\Query\FilterType;

/**
 * Clase base para todos los controladores de la API
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
    use ApiResponseFormatter;

    /**
     * Define los filtros disponibles y su tipo de comportamiento.
     *
     * Tipos soportados:
     * - exact: Coincidencia exacta (field = value)
     * - partial: Coincidencia parcial (LIKE %value%)
     * - in: Lista de valores (WHERE IN)
     * - range: Rango de valores (fechas o números)
     * - simple_search: Búsqueda simple (autocomplete)
     * - global_search: Búsqueda global multi-campo
     *
     * Ejemplo:
     * [
     *   'status' => 'exact',
     *   'name' => 'partial',
     *   'search' => 'global_search',
     * ]
     *
     * @return array<string, string>
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * Obtiene el campo predeterminado para ordenamiento
     *
     * Este método debe ser sobrescrito en los controladores hijos
     * para definir el campo predeterminado para ordenamiento.
     *
     * @return string Nombre del campo para ordenamiento predeterminado
     */
    protected function getDefaultSortField(): string
    {
        return 'created_at';
    }

    /**
     * Obtiene el orden predeterminado para ordenamiento
     *
     * Este método debe ser sobrescrito en los controladores hijos
     * para definir el orden predeterminado para ordenamiento.
     *
     * @return string Dirección del ordenamiento ('asc' o 'desc')
     */
    protected function getDefaultSortOrder(): string
    {
        return 'desc';
    }

    /**
     * Extrae los parámetros de filtrado de la solicitud
     *
     * @param  Request  $request  Solicitud HTTP
     * @return array Parámetros de filtrado extraídos
     */
    protected function getFilterParams(Request $request): array
    {
        $filters = [];
        $filterDefinitions = $this->filters();

        foreach ($request->query() as $key => $value) {
            if (isset($filterDefinitions[$key]) && $value !== '') {
                $type = $filterDefinitions[$key];
                
                // Estructura normalizada para el servicio: [ 'value' => X, 'type' => Y ]
                $filters[$key] = [
                    'value' => $value,
                    'type' => $type
                ];
            }
        }

        return $filters;
    }

    /**
     * Extrae los parámetros de ordenamiento de la solicitud
     *
     * @param  Request  $request  Solicitud HTTP
     * @return array Parámetros de ordenamiento (sort_by y sort_order)
     */
    protected function getSortingParams(Request $request): array
    {
        return [
            'sort_by' => $request->query('sort_by', $this->getDefaultSortField()),
            'sort_order' => $request->query('sort_order', $this->getDefaultSortOrder()),
        ];
    }

    /**
     * Extrae los parámetros de paginación de la solicitud
     *
     * @param  Request  $request  Solicitud HTTP
     * @return array Parámetros de paginación (page y per_page)
     */
    protected function getPaginationParams(Request $request): array
    {
        return [
            'page' => (int) $request->query('page', 1),
            'per_page' => (int) $request->query('per_page', 10),
        ];
    }

    /**
     * Extrae los parámetros de rango de la solicitud.
     *
     * Busca parámetros en la URL probando 4 patrones de nomenclatura diferentes.
     *
     * Patrones soportados (usando 'price' como prefijo de ejemplo):
     * 1. Sufijos de fecha:   price_start / price_end  (Prioridad Alta)
     * 2. Prefijos de fecha:  start_price / end_price  (Prioridad Alta)
     * 3. Sufijos numéricos:  price_min   / price_max  (Prioridad Baja)
     * 4. Prefijos numéricos: min_price   / max_price  (Prioridad Baja)
     *
     * Nota: El sistema devuelve el primer par que encuentre con datos,
     * priorizando los estilos de 'fecha' (start/end) sobre los 'numéricos' (min/max).
     *
     * @param  Request  $request  Solicitud HTTP
     * @param  string   $prefix   Prefijo del parámetro definido en supportedRanges (ej: 'date', 'price')
     * @return array  Array normalizado: ['start'=>..., 'end'=>...] o ['min'=>..., 'max'=>...].
     */
    protected function getRangeParams(Request $request, string $prefix = 'date'): array
    {
        // Intentar primero con start/end (fechas)
        $start = $request->query("{$prefix}_start") ?? $request->query("start_{$prefix}");
        $end = $request->query("{$prefix}_end") ?? $request->query("end_{$prefix}");

        // Si no hay start/end, intentar con min/max (números)
        $min = $request->query("{$prefix}_min") ?? $request->query("min_{$prefix}");
        $max = $request->query("{$prefix}_max") ?? $request->query("max_{$prefix}");

        // Retornar el formato que tenga datos
        if ($start !== null || $end !== null) {
            return [
                'start' => $start && $start !== '' ? $start : null,
                'end' => $end && $end !== '' ? $end : null,
            ];
        }

        if ($min !== null || $max !== null) {
            return [
                'min' => $min !== '' ? $min : null,
                'max' => $max !== '' ? $max : null,
            ];
        }

        return [];
    }

    /**
     * Combina todos los parámetros de consulta en un único array
     *
     * @param  Request  $request  Solicitud HTTP
     * @return array Parámetros combinados de paginación, ordenamiento, filtros y rango de fechas
     */
    protected function getQueryParams(Request $request): array
    {
        return [
            ...$this->getPaginationParams($request),
            ...$this->getSortingParams($request),
            'filters' => array_merge(
                $this->getFilterParams($request),
                $this->getRangeFilters($request)
            ),
        ];
    }

    /**
     * Procesa los rangos configurados en supportedRanges y los convierte a filtros.
     *
     * Itera sobre cada configuración de rango soportado, extrae los valores
     * del request y los formatea para ser consumidos por el servicio.
     *
     * @param  Request  $request  Solicitud HTTP
     * @return array  Array de filtros compatible con Service::applyFilters
     *                Ej: ['amount' => ['type' => 'range', 'value' => [...]]]
     */
    protected function getRangeFilters(Request $request): array
    {
        $filters = [];

        foreach ($this->supportedRanges() as $prefix => $field) {
            $range = $this->getRangeParams($request, $prefix);

            if (! empty($range)) {
                $filters[$field] = [
                    'type' => FilterType::RANGE,
                    'value' => $range,
                ];
            }
        }

        return $filters;
    }

    /**
     * Define los rangos soportados y su mapeo a campos de base de datos.
     *
     * Este método debe ser sobrescrito por los controladores hijos para habilitar
     * la funcionalidad de rangos (fechas, precios, edades, etc).
     *
     * Formato: ['prefijo_url' => 'columna_bd']
     *
     * Ejemplo:
     * return [
     *     'created_at' => 'created_at', // ?created_at_start=...
     *     'price'      => 'final_amount' // ?price_min=...
     * ];
     *
     * @return array<string, string> Mapa de prefijos a columnas
     */
    protected function supportedRanges(): array
    {
        return [
            'date' => 'created_at',
        ];
    }
}
