<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Events;

use App\Events\UserRegistered as LaravelUserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener que maneja el evento de registro de usuario de Laravel
 * y ejecuta las acciones necesarias
 */
class LaravelUserRegisteredListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(LaravelUserRegistered $event): void
    {
        // Aquí se pueden ejecutar las acciones necesarias después del registro
        // Por ejemplo, enviar email de bienvenida, crear configuración inicial, etc.
        // Los listeners originales de Laravel seguirán funcionando
    }
}


