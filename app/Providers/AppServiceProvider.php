<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Macro para agregar columnas de auditoría fácilmente
        Blueprint::macro('auditable', function () {
            /** @var Blueprint $this */
            $this->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $this->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $this->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });

        // Macro para eliminar columnas de auditoría
        Blueprint::macro('dropAuditable', function () {
            /** @var Blueprint $this */
            $this->dropForeign(['created_by']);
            $this->dropForeign(['updated_by']);
            $this->dropForeign(['deleted_by']);
            $this->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });
    }
}
