<?php

declare(strict_types=1);

namespace Src\Task\Application\UseCases;

use Src\Task\Domain\Repositories\TaskRepositoryInterface;

class DeleteTaskUseCase
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Ejecuta el caso de uso para eliminar una tarea
     *
     * @param int $id ID de la tarea
     * @return bool
     * @throws \Src\Task\Domain\Exceptions\TaskNotFoundException
     */
    public function execute(int $id): bool
    {
        // Verificar que la tarea existe antes de eliminar
        $this->taskRepository->findById($id);

        return $this->taskRepository->delete($id);
    }
}


