<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Traits\ApiResponseFormatter;

/**
 * Clase base para todos los controladores de la API
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
    use ApiResponseFormatter;

    /**
     * Define los filtros permitidos para consultas
     *
     * Este método debe ser sobrescrito en los controladores hijos
     * para definir qué campos pueden ser utilizados como filtros.
     *
     * @return array Lista de campos permitidos para filtrar
     */
    protected function getAllowedFilters(): array
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
        $allowedFilters = $this->getAllowedFilters();

        foreach ($request->query() as $key => $value) {
            if (in_array($key, $allowedFilters) && $value !== '') {
                $filters[$key] = $value;
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
     * Combina todos los parámetros de consulta en un único array
     *
     * @param  Request  $request  Solicitud HTTP
     * @return array Parámetros combinados de paginación, ordenamiento y filtros
     */
    protected function getQueryParams(Request $request): array
    {
        return [
            ...$this->getPaginationParams($request),
            ...$this->getSortingParams($request),
            'filters' => $this->getFilterParams($request),
        ];
    }
}
