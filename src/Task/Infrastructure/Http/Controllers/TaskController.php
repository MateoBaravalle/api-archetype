<?php

declare(strict_types=1);

namespace Src\Task\Infrastructure\Http\Controllers;

use Src\Task\Infrastructure\Http\Requests\TaskRequest;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Src\Shared\Infrastructure\ApiResponseFormatter;
use Src\Task\Application\UseCases\CreateTaskUseCase;
use Src\Task\Application\UseCases\DeleteTaskUseCase;
use Src\Task\Application\UseCases\GetTaskByIdUseCase;
use Src\Task\Application\UseCases\GetTasksUseCase;
use Src\Task\Application\UseCases\UpdateTaskUseCase;
use Src\Task\Domain\Exceptions\TaskNotFoundException;

class TaskController extends BaseController
{
    use ApiResponseFormatter;
    use AuthorizesRequests;
    use ValidatesRequests;

    private GetTasksUseCase $getTasksUseCase;
    private GetTaskByIdUseCase $getTaskByIdUseCase;
    private CreateTaskUseCase $createTaskUseCase;
    private UpdateTaskUseCase $updateTaskUseCase;
    private DeleteTaskUseCase $deleteTaskUseCase;

    public function __construct(
        GetTasksUseCase $getTasksUseCase,
        GetTaskByIdUseCase $getTaskByIdUseCase,
        CreateTaskUseCase $createTaskUseCase,
        UpdateTaskUseCase $updateTaskUseCase,
        DeleteTaskUseCase $deleteTaskUseCase
    ) {
        $this->getTasksUseCase = $getTasksUseCase;
        $this->getTaskByIdUseCase = $getTaskByIdUseCase;
        $this->createTaskUseCase = $createTaskUseCase;
        $this->updateTaskUseCase = $updateTaskUseCase;
        $this->deleteTaskUseCase = $deleteTaskUseCase;
    }

    /**
     * Mostrar un listado de tareas.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $this->getFilterParams($request);
            $sortBy = $request->query('sort_by', 'priority');
            $sortDirection = $request->query('sort_order', 'desc');
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 15);

            $result = $this->getTasksUseCase->execute($filters, $sortBy, $sortDirection, $page, $perPage);

            // Convertir entidades a arrays
            $tasksArray = array_map(fn ($task) => $task->toArray(), $result['data']);

            return $this->successResponse([
                'data' => $tasksArray,
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'per_page' => $result['per_page'],
                    'last_page' => $result['last_page'],
                ],
            ]);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Almacenar una nueva tarea.
     */
    public function store(TaskRequest $request): JsonResponse
    {
        try {
            $task = $this->createTaskUseCase->execute($request->validated());

            return $this->successResponse(
                $task->toArray(),
                'Tarea creada correctamente',
                201
            );
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Mostrar una tarea específica.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $task = $this->getTaskByIdUseCase->execute($id);

            return $this->successResponse($task->toArray());
        } catch (TaskNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Actualizar una tarea específica.
     */
    public function update(TaskRequest $request, int $id): JsonResponse
    {
        try {
            $task = $this->updateTaskUseCase->execute($id, $request->validated());

            return $this->successResponse(
                $task->toArray(),
                'Tarea actualizada correctamente'
            );
        } catch (TaskNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Eliminar una tarea específica.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deleteTaskUseCase->execute($id);

            return $this->successResponse(
                null,
                'Tarea eliminada correctamente'
            );
        } catch (TaskNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Extrae los parámetros de filtrado de la solicitud
     */
    private function getFilterParams(Request $request): array
    {
        $filters = [];
        $allowedFilters = ['global', 'status', 'priority'];

        foreach ($request->query() as $key => $value) {
            if (in_array($key, $allowedFilters) && $value !== '') {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }
}

