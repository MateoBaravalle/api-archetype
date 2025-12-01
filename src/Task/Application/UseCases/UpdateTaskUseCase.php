<?php

declare(strict_types=1);

namespace Src\Task\Application\UseCases;

use DateTimeImmutable;
use Src\Task\Domain\Entities\Task;
use Src\Task\Domain\Repositories\TaskRepositoryInterface;

class UpdateTaskUseCase
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Ejecuta el caso de uso para actualizar una tarea
     *
     * @param int $id ID de la tarea
     * @param array $data Datos a actualizar: ['title', 'description', 'status', 'due_date', 'priority']
     * @return Task
     * @throws \Src\Task\Domain\Exceptions\TaskNotFoundException
     * @throws \InvalidArgumentException
     */
    public function execute(int $id, array $data): Task
    {
        $task = $this->taskRepository->findById($id);

        $dueDate = null;
        if (isset($data['due_date']) && $data['due_date']) {
            $dueDate = new DateTimeImmutable($data['due_date']);
        }

        $task->update(
            $data['title'],
            $data['description'] ?? null,
            $data['status'],
            $dueDate,
            $data['priority']
        );

        return $this->taskRepository->update($task);
    }
}


