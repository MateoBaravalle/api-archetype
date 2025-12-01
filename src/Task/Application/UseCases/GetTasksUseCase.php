<?php

declare(strict_types=1);

namespace Src\Task\Application\UseCases;

use Src\Task\Domain\Repositories\TaskRepositoryInterface;

class GetTasksUseCase
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Ejecuta el caso de uso para obtener tareas con filtros
     *
     * @param array $filters Filtros: ['status' => string, 'priority' => int, 'global' => string]
     * @param string $sortBy Campo por el cual ordenar
     * @param string $sortDirection Dirección del ordenamiento (asc|desc)
     * @param int $page Número de página
     * @param int $perPage Elementos por página
     * @return array ['data' => Task[], 'total' => int, 'page' => int, 'per_page' => int]
     */
    public function execute(
        array $filters = [],
        string $sortBy = 'priority',
        string $sortDirection = 'desc',
        int $page = 1,
        int $perPage = 15
    ): array {
        return $this->taskRepository->findAll(
            $filters,
            [$sortBy, $sortDirection],
            $page,
            $perPage
        );
    }
}


