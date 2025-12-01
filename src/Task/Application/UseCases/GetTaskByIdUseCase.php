<?php

declare(strict_types=1);

namespace Src\Task\Application\UseCases;

use Src\Task\Domain\Entities\Task;
use Src\Task\Domain\Repositories\TaskRepositoryInterface;

class GetTaskByIdUseCase
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Ejecuta el caso de uso para obtener una tarea por ID
     *
     * @throws \Src\Task\Domain\Exceptions\TaskNotFoundException
     */
    public function execute(int $id): Task
    {
        return $this->taskRepository->findById($id);
    }
}


