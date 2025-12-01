<?php

declare(strict_types=1);

namespace Src\Task\Application\UseCases;

use DateTimeImmutable;
use Src\Task\Domain\Entities\Task;
use Src\Task\Domain\Repositories\TaskRepositoryInterface;

class CreateTaskUseCase
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Ejecuta el caso de uso para crear una nueva tarea
     *
     * @param array $data Datos de la tarea: ['title', 'description', 'status', 'due_date', 'priority']
     * @return Task
     * @throws \InvalidArgumentException
     */
    public function execute(array $data): Task
    {
        $dueDate = null;
        if (isset($data['due_date']) && $data['due_date']) {
            $dueDate = new DateTimeImmutable($data['due_date']);
        }

        $task = Task::create(
            $data['title'],
            $data['description'] ?? null,
            $data['status'] ?? 'pending',
            $dueDate,
            $data['priority'] ?? 1
        );

        return $this->taskRepository->save($task);
    }
}


