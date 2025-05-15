<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

/**
 * Clase base abstracta para servicios
 *
 * Proporciona las operaciones básicas CRUD y funcionalidades comunes
 * para filtrado, ordenamiento y consulta de datos.
 */
abstract class Service
{
    /**
     * Constructor
     *
     * @param  Model  $model  El modelo Eloquent asociado a este servicio
     */
    public function __construct(
        protected Model $model
    ) {}

    // ==============================================
    // Métodos básicos CRUD
    // ==============================================

    /**
     * Obtiene una colección paginada de registros
     *
     * @param  int  $page  Número de página actual
     * @param  int  $perPage  Cantidad de registros por página
     * @param  Builder|null  $query  Consulta personalizada (opcional)
     * @return LengthAwarePaginator Colección paginada de registros
     */
    protected function getAll(int $page, int $perPage, ?Builder $query = null): LengthAwarePaginator
    {
        return ($query ?? $this->model->query())->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Obtiene un registro por su ID
     *
     * @param  int  $id  ID del registro a buscar
     * @return Model Registro encontrado
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si el registro no existe
     */
    protected function getById(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Obtiene un registro por su ID con relaciones cargadas
     *
     * @param  int  $id  ID del registro a buscar
     * @param  array  $relation  Arreglo con las relaciones a cargar
     * @return Model Registro encontrado con sus relaciones
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si el registro no existe
     */
    protected function getByIdWith(int $id, array $relation): Model
    {
        return $this->model->with($relation)->findOrFail($id);
    }

    /**
     * Crea un nuevo registro
     *
     * @param  array  $data  Datos para crear el registro
     * @return Model Registro creado
     */
    protected function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Actualiza un registro existente
     *
     * @param  int  $id  ID del registro a actualizar
     * @param  array  $data  Datos para actualizar el registro
     * @return Model Registro actualizado
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si el registro no existe
     */
    protected function update(int $id, array $data): Model
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model->fresh();
    }

    /**
     * Elimina un registro
     *
     * @param  int  $id  ID del registro a eliminar
     * @return bool Indicador de éxito
     */
    protected function delete(int $id): bool
    {
        return (bool) $this->model->destroy($id);
    }

    // ==============================================
    // Métodos de consulta principal
    // ==============================================

    /**
     * Aplica filtros y ordenamiento a una consulta
     *
     * @param  Builder  $query  Consulta a la que aplicar filtros y ordenamiento
     * @param  array  $params  Parámetros con filtros, ordenamiento y rango de fechas
     * @return Builder Consulta con filtros y ordenamiento aplicados
     */
    protected function getFilteredAndSorted(Builder $query, array $params): Builder
    {
        if (! empty($params['filters'])) {
            $this->applyFilters($query, $params['filters']);
        }

        if (! empty($params['date_range'])) {
            $this->applyDateRangeFilter($query, $params['date_range']);
        }

        $this->applySorting($query, $params['sort_by'], $params['sort_order']);

        return $query;
    }

    // ==============================================
    // Métodos de ordenamiento
    // ==============================================

    /**
     * Aplica el ordenamiento a una consulta
     *
     * @param  Builder  $query  Consulta a la que aplicar el ordenamiento
     * @param  string  $sortBy  Campo por el que ordenar
     * @param  string  $sortOrder  Dirección del ordenamiento ('asc' o 'desc')
     * @return Builder Consulta con ordenamiento aplicado
     */
    protected function applySorting(Builder $query, string $sortBy, string $sortOrder): Builder
    {
        $method = 'sortBy'.ucfirst($sortBy);

        if (method_exists($this, $method)) {
            $this->$method($query, $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }

    // ==============================================
    // Métodos de filtrado
    // ==============================================

    /**
     * Aplica los filtros a una consulta
     *
     * @param  Builder  $query  Consulta a la que aplicar los filtros
     * @param  array  $filters  Arreglo de filtros a aplicar
     * @return Builder Consulta con filtros aplicados
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        // Extraer y aplicar el filtro global si existe
        if (! empty($filters['global'])) {
            $this->applyGlobalSearch($query, $filters['global']);
            unset($filters['global']);
        }

        foreach ($filters as $field => $value) {
            // Ignorar valores vacíos o nulos
            if ($value === '' || $value === null) {
                continue;
            }

            // Si 'global' se pasó como un nombre de campo, ignorarlo para evitar errores
            if ($field === 'global') {
                continue;
            }

            $method = 'filterBy'.ucfirst($field);

            if (method_exists($this, $method)) {
                $this->$method($query, $value);
            } else {
                // Verificar que el campo existe en la tabla antes de usarlo
                $columns = Schema::getColumnListing($this->model->getTable());
                if (! in_array($field, $columns)) {
                    continue; // Ignorar campos que no existen en la tabla
                }

                if (is_string($value)) {
                    $value = strtolower($value);
                    $query->where($field, 'like', "%{$value}%");
                } elseif (is_bool($value)) {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Aplica un filtro de rango de fechas a la consulta
     *
     * @param  Builder  $query  La consulta a la que aplicar el filtro
     * @param  array  $dateRange  Array con las claves 'start' y 'end'
     * @param  string|null  $dateColumn  La columna de fecha a utilizar (si es null, usa getDateColumn)
     */
    protected function applyDateRangeFilter(Builder $query, array $dateRange, ?string $dateColumn = null): void
    {
        // Si no se especifica una columna, obtener la definida en getDateColumn
        $column = $dateColumn ?? $this->getDateColumn();

        if (! empty($dateRange['start']) && ! empty($dateRange['end'])) {
            $query->where(function ($q) use ($column, $dateRange) {
                // Incluir todos los registros donde la fecha sea mayor o igual a la fecha de inicio
                $q->whereDate($column, '>=', $dateRange['start']);
                // Y menor o igual a la fecha final
                $q->whereDate($column, '<=', $dateRange['end']);
            });
        } elseif (! empty($dateRange['start'])) {
            $query->whereDate($column, '>=', $dateRange['start']);
        } elseif (! empty($dateRange['end'])) {
            $query->whereDate($column, '<=', $dateRange['end']);
        }
    }

    /**
     * Devuelve la columna de fecha a utilizar para filtrar por rango de fechas
     *
     * Este método puede ser sobrescrito en las clases hijas para personalizar
     * la columna de fecha utilizada en los filtros de rango.
     *
     * @return string Nombre de la columna de fecha
     */
    protected function getDateColumn(): string
    {
        return 'created_at';
    }

    /**
     * Aplica una búsqueda global a la consulta
     *
     * @param  Builder  $query  La consulta a la que aplicar el filtro
     * @param  string  $value  El valor a buscar
     * @return Builder La consulta con el filtro aplicado
     */
    protected function applyGlobalSearch(Builder $query, string $value): Builder
    {
        $value = strtolower($value);

        $query->where(function ($q) use ($value) {
            // Aplicar búsqueda en columnas de texto
            foreach ($this->getGlobalSearchColumns() as $column) {
                $q->orWhere($column, 'like', "%{$value}%");
            }

            // Aplicar búsqueda en relaciones
            $this->applyGlobalSearchToRelations($q, $value);
        });

        return $query;
    }

    /**
     * Devuelve las columnas de texto en las que se puede buscar globalmente
     *
     * Este método debe ser sobrescrito en las clases hijas para definir
     * las columnas en las que se puede realizar búsqueda global.
     *
     * @return array Lista de columnas de texto
     */
    protected function getGlobalSearchColumns(): array
    {
        return ['name', 'description'];
    }

    /**
     * Aplica la búsqueda global a las relaciones definidas
     *
     * @param  Builder  $query  La consulta a la que aplicar el filtro
     * @param  string  $value  El valor a buscar
     */
    protected function applyGlobalSearchToRelations(Builder $query, string $value): void
    {
        foreach ($this->getGlobalSearchRelations() as $relation => $columns) {
            $query->orWhereHas($relation, function ($q) use ($columns, $value) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$value}%");
                }
            });
        }
    }

    /**
     * Devuelve las relaciones y sus columnas para búsqueda global
     *
     * Este método debe ser sobrescrito en las clases hijas para definir
     * las relaciones y columnas en las que se puede realizar búsqueda global.
     *
     * @return array Array asociativo de relaciones y sus columnas
     */
    protected function getGlobalSearchRelations(): array
    {
        return [];
    }
}
