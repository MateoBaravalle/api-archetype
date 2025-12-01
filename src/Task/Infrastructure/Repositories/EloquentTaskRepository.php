<?php

declare(strict_types=1);

namespace Src\Task\Infrastructure\Repositories;

use DateTimeImmutable;
use Src\Task\Domain\Entities\Task;
use Src\Task\Domain\Exceptions\TaskNotFoundException;
use Src\Task\Domain\Repositories\TaskRepositoryInterface;
use Src\Task\Domain\ValueObjects\TaskPriority;
use Src\Task\Domain\ValueObjects\TaskStatus;
use Src\Task\Infrastructure\Persistence\EloquentTaskModel;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    private EloquentTaskModel $model;

    public function __construct(EloquentTaskModel $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): Task
    {
        $eloquentTask = $this->model->find($id);

        if (! $eloquentTask) {
            throw new TaskNotFoundException($id);
        }

        return $this->toDomain($eloquentTask);
    }

    public function findAll(array $filters = [], array $sort = ['priority', 'desc'], int $page = 1, int $perPage = 15): array
    {
        $query = $this->model->newQuery();

        // Aplicar filtros
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['global'])) {
            $searchTerm = $filters['global'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Aplicar ordenamiento
        $query->orderBy($sort[0], $sort[1]);

        // Paginación
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $tasks = [];
        foreach ($paginator->items() as $eloquentTask) {
            $tasks[] = $this->toDomain($eloquentTask);
        }

        return [
            'data' => $tasks,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function save(Task $task): Task
    {
        $eloquentTask = $this->model->create([
            'title' => $task->title(),
            'description' => $task->description(),
            'status' => $task->status()->value(),
            'due_date' => $task->dueDate()?->format('Y-m-d'),
            'priority' => $task->priority()->value(),
        ]);

        return $this->toDomain($eloquentTask);
    }

    public function update(Task $task): Task
    {
        $eloquentTask = $this->model->find($task->id());

        if (! $eloquentTask) {
            throw new TaskNotFoundException($task->id());
        }

        $eloquentTask->update([
            'title' => $task->title(),
            'description' => $task->description(),
            'status' => $task->status()->value(),
            'due_date' => $task->dueDate()?->format('Y-m-d'),
            'priority' => $task->priority()->value(),
        ]);

        return $this->toDomain($eloquentTask->fresh());
    }

    public function delete(int $id): bool
    {
        $eloquentTask = $this->model->find($id);

        if (! $eloquentTask) {
            throw new TaskNotFoundException($id);
        }

        return $eloquentTask->delete();
    }

    /**
     * Convierte un modelo de Eloquent a entidad de dominio
     */
    private function toDomain(EloquentTaskModel $eloquentTask): Task
    {
        return new Task(
            $eloquentTask->id,
            $eloquentTask->title,
            $eloquentTask->description,
            new TaskStatus($eloquentTask->status),
            $eloquentTask->due_date ? new DateTimeImmutable($eloquentTask->due_date->format('Y-m-d')) : null,
            new TaskPriority($eloquentTask->priority),
            new DateTimeImmutable($eloquentTask->created_at->format('Y-m-d H:i:s')),
            new DateTimeImmutable($eloquentTask->updated_at->format('Y-m-d H:i:s'))
        );
    }
}


