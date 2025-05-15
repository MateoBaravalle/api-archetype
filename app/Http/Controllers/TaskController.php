<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Servicio de tareas
     *
     * @var TaskService
     */
    protected $taskService;

    /**
     * Constructor
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Mostrar un listado de tareas.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $tasks = $this->taskService->getTasks($params);

            return $this->successResponse(
                $this->transformCollection($tasks)
            );
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Almacenar una nueva tarea.
     */
    public function store(TaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->createTask($request->validated());

            return $this->successResponse(
                $this->transformResource($task),
                'Tarea creada correctamente',
                201
            );
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Mostrar una tarea específica.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->getTask($id);

            return $this->successResponse(
                $this->transformResource($task)
            );
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Actualizar una tarea específica.
     */
    public function update(TaskRequest $request, int $id): JsonResponse
    {
        try {
            $task = $this->taskService->updateTask($id, $request->validated());

            return $this->successResponse(
                $this->transformResource($task),
                'Tarea actualizada correctamente'
            );
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Eliminar una tarea específica.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->taskService->deleteTask($id);

            return $this->successResponse(
                null,
                'Tarea eliminada correctamente'
            );
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Definir los filtros permitidos
     */
    protected function getAllowedFilters(): array
    {
        return ['global', 'status', 'priority'];
    }

    /**
     * Definir el campo de ordenamiento por defecto
     */
    protected function getDefaultSortField(): string
    {
        return 'priority';
    }
}
