<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    /**
     * Constructor
     */
    public function __construct(
        protected Task $model
    ) {}

    /**
     * Gets tasks with filters applied
     *
     * @param  array  $params  Parameters for filtering, sorting, and pagination
     * @return LengthAwarePaginator Paginated collection of tasks
     */
    public function getTasks(array $params): LengthAwarePaginator
    {
        return $this->model
            ->filterAndSort($params)
            ->paginateFromParams($params);
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
        return $this->model->findOrFail($id);
    }

    /**
     * Creates a new task
     *
     * @param  array  $data  Task data
     * @return Task Task created
     */
    public function createTask(array $data): Task
    {
        return $this->model->create($data);
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
        $task = $this->getTask($id);
        $task->update($data);

        return $task->fresh();
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
        $task = $this->getTask($id);

        return (bool) $task->delete();
    }
}
