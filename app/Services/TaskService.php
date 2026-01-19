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
     * Gets tasks with filters applied
     *
     * @param  array  $params  Parameters for filtering, sorting, and pagination
     * @return LengthAwarePaginator Paginated collection of tasks
     */
    public function getTasks(array $params): LengthAwarePaginator
    {
        $query = $this->model->query();

        $query = $this->getFilteredAndSorted($query, $params);

        return $this->getAll($params['page'], $params['per_page'], $query);
    }

    /**
     * Gets a task by ID
     *
     * @param  int  $id  Task ID
     * @return Task Task found
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getTask(int $id): Task
    {
        return $this->getById($id);
    }

    /**
     * Creates a new task
     *
     * @param  array  $data  Task data
     * @return Task Task created
     */
    public function createTask(array $data): Task
    {
        return $this->create($data);
    }

    /**
     * Updates an existing task
     *
     * @param  int  $id  Task ID
     * @param  array  $data  Updated data
     * @return Task Task updated
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateTask(int $id, array $data): Task
    {
        return $this->update($id, $data);
    }

    /**
     * Deletes a task
     *
     * @param  int  $id  Task ID
     * @return bool Success indicator
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
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
