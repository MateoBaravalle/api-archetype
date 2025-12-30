<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\Query\FilterType;

class TaskController extends Controller
{
    /**
     * Constructor
     */
    public function __construct(
        protected readonly TaskService $taskService
    ) {}

    /**
     * Mostrar un listado de tareas.
     */
    public function index(Request $request): JsonResponse
    {
        $params = $this->getQueryParams($request);
        $tasks = $this->taskService->getTasks($params);

        return $this->successResponse($tasks);
    }

    /**
     * Almacenar una nueva tarea.
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return $this->successResponse($task, 'Tarea creada correctamente', 201);
    }

    /**
     * Mostrar una tarea específica.
     */
    public function show(int $id): JsonResponse
    {
        $task = $this->taskService->getTask($id);
        
        $this->authorize('view', $task);

        return $this->successResponse($task);
    }

    /**
     * Actualizar una tarea específica.
     */
    public function update(TaskRequest $request, int $id): JsonResponse
    {
        $task = $this->taskService->getTask($id); // Obtenemos el modelo primero para la policy
        
        $this->authorize('update', $task);

        $task = $this->taskService->updateTask($id, $request->validated());

        return $this->successResponse($task, 'Tarea actualizada correctamente');
    }

    /**
     * Eliminar una tarea específica.
     */
    public function destroy(int $id): JsonResponse
    {
        $task = $this->taskService->getTask($id); // Obtenemos el modelo primero
        
        $this->authorize('delete', $task);
        
        $this->taskService->deleteTask($id);

        return $this->successResponse(
            null,
            'Tarea eliminada correctamente'
        );
    }

    /**
     * Definir los filtros permitidos
     */
    protected function filters(): array
    {
        return [
            'global' => FilterType::GLOBAL_SEARCH,
            'status' => FilterType::EXACT,
            'priority' => FilterType::EXACT,
        ];
    }

    /**
     * Definir el campo de ordenamiento por defecto
     */
    protected function getDefaultSortField(): string
    {
        return 'priority';
    }
}
