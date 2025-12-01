<?php

declare(strict_types=1);

namespace Src\Task\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Task\Application\UseCases\CreateTaskUseCase;
use Src\Task\Application\UseCases\DeleteTaskUseCase;
use Src\Task\Application\UseCases\GetTaskByIdUseCase;
use Src\Task\Application\UseCases\GetTasksUseCase;
use Src\Task\Application\UseCases\UpdateTaskUseCase;
use Src\Task\Domain\Repositories\TaskRepositoryInterface;
use Src\Task\Infrastructure\Repositories\EloquentTaskRepository;

class TaskServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            TaskRepositoryInterface::class,
            EloquentTaskRepository::class
        );

        // Register use cases
        $this->app->singleton(GetTasksUseCase::class, function ($app) {
            return new GetTasksUseCase(
                $app->make(TaskRepositoryInterface::class)
            );
        });

        $this->app->singleton(GetTaskByIdUseCase::class, function ($app) {
            return new GetTaskByIdUseCase(
                $app->make(TaskRepositoryInterface::class)
            );
        });

        $this->app->singleton(CreateTaskUseCase::class, function ($app) {
            return new CreateTaskUseCase(
                $app->make(TaskRepositoryInterface::class)
            );
        });

        $this->app->singleton(UpdateTaskUseCase::class, function ($app) {
            return new UpdateTaskUseCase(
                $app->make(TaskRepositoryInterface::class)
            );
        });

        $this->app->singleton(DeleteTaskUseCase::class, function ($app) {
            return new DeleteTaskUseCase(
                $app->make(TaskRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}


