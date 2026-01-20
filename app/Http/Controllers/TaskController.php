<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\TaskRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
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
     * Display a listing of tasks.
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $tasks = $this->taskService->getTasks($request->validated());

        return $this->successResponse($tasks);
    }

    /**
     * Store a new task.
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return $this->successResponse($task, 'Task created successfully', 201);
    }

    /**
     * Display a specific task.
     */
    public function show(int $id): JsonResponse
    {
        $task = $this->taskService->getTask($id);
        
        $this->authorize('view', $task);

        return $this->successResponse($task);
    }

    /**
     * Update a specific task.
     */
    public function update(TaskRequest $request, int $id): JsonResponse
    {
        $task = $this->taskService->getTask($id); // We get the model first for the policy
        
        $this->authorize('update', $task);

        $task = $this->taskService->updateTask($id, $request->validated());

        return $this->successResponse($task, 'Task updated successfully');
    }

    /**
     * Delete a specific task.
     */
    public function destroy(int $id): JsonResponse
    {
        $task = $this->taskService->getTask($id); // We get the model first
        
        $this->authorize('delete', $task);
        
        $this->taskService->deleteTask($id);

        return $this->successResponse(
            null,
            'Task deleted successfully'
        );
    }
}
