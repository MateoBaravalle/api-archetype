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
     * Extrae los parámetros de rango de fechas de la solicitud
     *
     * @param  Request  $request  Solicitud HTTP
     * @return array Parámetros de rango de fechas (start y end)
     */
    protected function getDateRangeParams(Request $request): array
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return [
            'start' => $startDate && $startDate !== '' ? $startDate : null,
            'end' => $endDate && $endDate !== '' ? $endDate : null,
        ];
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
            'filters' => $this->getFilterParams($request),
            'date_range' => $this->getDateRangeParams($request),
        ];
    }
}
