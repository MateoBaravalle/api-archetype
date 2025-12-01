<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Auth\Application\UseCases\GetAuthenticatedUserUseCase;
use Src\Auth\Application\UseCases\LoginUseCase;
use Src\Auth\Application\UseCases\LogoutUseCase;
use Src\Auth\Application\UseCases\RegisterUseCase;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;
use Src\Auth\Domain\Services\PasswordHasherInterface;
use Src\Auth\Domain\Services\TokenServiceInterface;
use Src\Auth\Infrastructure\Repositories\EloquentUserRepository;
use Src\Auth\Infrastructure\Services\LaravelPasswordHasher;
use Src\Auth\Infrastructure\Services\SanctumTokenService;
use Src\User\Application\UseCases\GetUserProfileUseCase;
use Src\User\Application\UseCases\UpdateUserPasswordUseCase;
use Src\User\Application\UseCases\UpdateUserProfileUseCase;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(
            PasswordHasherInterface::class,
            LaravelPasswordHasher::class
        );

        $this->app->bind(
            TokenServiceInterface::class,
            SanctumTokenService::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        // Register Auth use cases
        $this->app->singleton(RegisterUseCase::class, function ($app) {
            return new RegisterUseCase(
                $app->make(UserRepositoryInterface::class),
                $app->make(PasswordHasherInterface::class)
            );
        });

        $this->app->singleton(LoginUseCase::class, function ($app) {
            return new LoginUseCase(
                $app->make(UserRepositoryInterface::class),
                $app->make(PasswordHasherInterface::class)
            );
        });

        $this->app->singleton(LogoutUseCase::class, function ($app) {
            return new LogoutUseCase(
                $app->make(TokenServiceInterface::class)
            );
        });

        $this->app->singleton(GetAuthenticatedUserUseCase::class, function ($app) {
            return new GetAuthenticatedUserUseCase(
                $app->make(UserRepositoryInterface::class)
            );
        });

        // Register User use cases
        $this->app->singleton(GetUserProfileUseCase::class, function ($app) {
            return new GetUserProfileUseCase(
                $app->make(UserRepositoryInterface::class)
            );
        });

        $this->app->singleton(UpdateUserProfileUseCase::class, function ($app) {
            return new UpdateUserProfileUseCase(
                $app->make(UserRepositoryInterface::class)
            );
        });

        $this->app->singleton(UpdateUserPasswordUseCase::class, function ($app) {
            return new UpdateUserPasswordUseCase(
                $app->make(UserRepositoryInterface::class)
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


