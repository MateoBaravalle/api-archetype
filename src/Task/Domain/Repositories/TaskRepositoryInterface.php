<?php

declare(strict_types=1);

namespace Src\Task\Domain\Repositories;

use Src\Task\Domain\Entities\Task;

interface TaskRepositoryInterface
{
    /**
     * Encuentra una tarea por su ID
     *
     * @throws \Src\Task\Domain\Exceptions\TaskNotFoundException
     */
    public function findById(int $id): Task;

    /**
     * Obtiene todas las tareas con paginación y filtros
     *
     * @param array $filters Filtros a aplicar (status, priority, search)
     * @param array $sort Ordenamiento [field, direction]
     * @param int $page Número de página
     * @param int $perPage Elementos por página
     * @return array ['data' => Task[], 'total' => int, 'page' => int, 'per_page' => int]
     */
    public function findAll(array $filters = [], array $sort = ['priority', 'desc'], int $page = 1, int $perPage = 15): array;

    /**
     * Guarda una nueva tarea
     */
    public function save(Task $task): Task;

    /**
     * Actualiza una tarea existente
     */
    public function update(Task $task): Task;

    /**
     * Elimina una tarea por su ID
     */
    public function delete(int $id): bool;
}


