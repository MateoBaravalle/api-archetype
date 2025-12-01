<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    Src\Task\Infrastructure\Providers\TaskServiceProvider::class,
    Src\Auth\Infrastructure\Providers\AuthServiceProvider::class,
];
