<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService extends Service
{
    /**
     * Constructor
     */
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /**
     * Obtiene tareas con filtros aplicados
     *
     * @param  array  $params  Parámetros para filtrado, ordenamiento y paginación
     * @return LengthAwarePaginator Colección paginada de tareas
     */
    public function getTasks(array $params): LengthAwarePaginator
    {
        $query = $this->model->query();

        $query = $this->getFilteredAndSorted($query, $params);

        return $this->getAll($params['page'], $params['per_page'], $query);
    }

    /**
     * Obtiene una tarea por ID
     *
     * @param  int  $id  ID de la tarea
     * @return Task Tarea encontrada
     */
    public function getTask(int $id): Task
    {
        return $this->getById($id);
    }

    /**
     * Crea una nueva tarea
     *
     * @param  array  $data  Datos de la tarea
     * @return Task Tarea creada
     */
    public function createTask(array $data): Task
    {
        return $this->create($data);
    }

    /**
     * Actualiza una tarea existente
     *
     * @param  int  $id  ID de la tarea
     * @param  array  $data  Datos actualizados
     * @return Task Tarea actualizada
     */
    public function updateTask(int $id, array $data): Task
    {
        return $this->update($id, $data);
    }

    /**
     * Elimina una tarea
     *
     * @param  int  $id  ID de la tarea
     * @return bool Indicador de éxito
     */
    public function deleteTask(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Filtra por estado
     */
    protected function filterByStatus(Builder $query, string $value): Builder
    {
        return $query->where('status', $value);
    }

    /**
     * Filtra por prioridad
     */
    protected function filterByPriority(Builder $query, int $value): Builder
    {
        return $query->where('priority', $value);
    }

    /**
     * Define las columnas para la búsqueda global
     */
    protected function getGlobalSearchColumns(): array
    {
        return ['title', 'description'];
    }
}
