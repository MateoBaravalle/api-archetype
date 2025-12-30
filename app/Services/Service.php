<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Support\Query\FilterType;

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
        protected readonly Model $model
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
        return DB::transaction(fn() => $this->model->create($data));
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
        return DB::transaction(function () use ($id, $data) {
            $model = $this->getById($id);
            $model->update($data);

            return $model->fresh();
        });
    }

    /**
     * Elimina un registro
     *
     * @param  int  $id  ID del registro a eliminar
     * @return bool Indicador de éxito
     */
    protected function delete(int $id): bool
    {
        return DB::transaction(fn() => (bool) $this->model->destroy($id));
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
        foreach ($filters as $field => $data) {
            $value = $data['value'];
            $type = $data['type'];

            // Ignorar valores vacíos
            if ($value === '' || $value === null) {
                continue;
            }

            // Normalización de tipos básicos
             if (is_string($value)) {
                if ($value === 'true') $value = true;
                if ($value === 'false') $value = false;
            }

            // Si hay un método específico en el servicio hijo, usarlo
            $method = 'filterBy' . Str::studly($field);
            if (method_exists($this, $method)) {
                $this->$method($query, $value);
                continue;
            }

            // Aplicar filtro según el tipo definido (SIN ADIVINAR)
            match ($type) {
                FilterType::EXACT => $query->where($field, $value),
                FilterType::PARTIAL => $query->where($field, 'like', '%' . $value . '%'),
                FilterType::IN => $query->whereIn($field, (array) $value),
                FilterType::GLOBAL_SEARCH => $this->applyGlobalSearch($query, $value),
                FilterType::SIMPLE_SEARCH => $this->applySimpleSearch($query, $value),
                FilterType::RANGE => $this->applyRangeFilter($query, $field, $value),
                default => $query->where($field, $value),
            };
        }

        return $query;
    }

    /**
     * Aplica un filtro de rango de fechas a la consulta
     *
     * @param  Builder  $query  La consulta a la que aplicar el filtro
    /**
     * Aplica un filtro de rango a la consulta (dates or numbers)
     *
     * @param  Builder  $query  La consulta
     * @param  string   $field  Nombre de la columna
     * @param  array    $range  Array con las claves 'start' y 'end' (o 'min'/'max')
     */
    protected function applyRangeFilter(Builder $query, string $field, array $range): void
    {
        // Soporte para claves start/end (fechas) y min/max (números)
        $start = $range['start'] ?? $range['min'] ?? null;
        $end = $range['end'] ?? $range['max'] ?? null;

        if ($start === null && $end === null) {
            return;
        }

        $query->where(function ($q) use ($field, $start, $end) {
            if ($start !== null) {
                // Estrategia híbrida: Si el campo termina en _at o _date, usamos whereDate. Si no, where normal.
                if (Str::endsWith($field, ['_at', '_date'])) {
                     $q->whereDate($field, '>=', $start);
                } else {
                     $q->where($field, '>=', $start);
                }
            }

            if ($end !== null) {
                if (Str::endsWith($field, ['_at', '_date'])) {
                     $q->whereDate($field, '<=', $end);
                } else {
                     $q->where($field, '<=', $end);
                }
            }
        });
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
        return [];
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

    /**
     * Aplica una búsqueda simple a la consulta (solo devuelve ID y campo de nombre)
     *
     * @param  Builder  $query  La consulta a la que aplicar el filtro
     * @param  string  $value  El valor a buscar
     * @return Builder La consulta con el filtro aplicado
     */
    protected function applySimpleSearch(Builder $query, string $value): Builder
    {
        $value = strtolower($value);
        $nameField = $this->getSimpleSearchNameField();
        $selectFields = $this->getSimpleSearchSelectFields();

        // Seleccionar los campos configurados
        $query->select($selectFields);

        // Aplicar filtro en el campo de nombre
        $query->where($nameField, 'like', "%{$value}%");

        return $query;
    }

    /**
     * Devuelve el campo que representa el "nombre" para búsquedas simples
     *
     * Este método debe ser sobrescrito en las clases hijas para definir
     * qué campo se considera como el "nombre" de la entidad.
     *
     * @return string Nombre del campo que representa el nombre de la entidad
     */
    protected function getSimpleSearchNameField(): string
    {
        return 'name';
    }

    /**
     * Devuelve los campos que se deben seleccionar en búsquedas simples
     *
     * Este método puede ser sobrescrito en las clases hijas para definir
     * qué campos se incluyen en la respuesta de búsqueda simple.
     *
     * @return array Lista de campos a seleccionar
     */
    protected function getSimpleSearchSelectFields(): array
    {
        return ['id', $this->getSimpleSearchNameField()];
    }
}
