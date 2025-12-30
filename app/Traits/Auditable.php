<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the Auditable trait.
     */
    public static function bootAuditable(): void
    {
        // Al crear: guardar created_by y updated_by
        static::creating(function (Model $model) {
            $userId = Auth::id();
            if ($userId) {
                $model->created_by = $userId;
                $model->updated_by = $userId;
            }
        });

        // Al actualizar: actualizar updated_by
        static::updating(function (Model $model) {
            $userId = Auth::id();
            if ($userId) {
                $model->updated_by = $userId;
            }
        });

        // Al borrar: si es SoftDelete, guardar deleted_by
        static::deleting(function (Model $model) {
            // Verificar si usa SoftDeletes y si tenemos usuario
            $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));
            $userId = Auth::id();

            if ($usesSoftDeletes && $userId && ! $model->isForceDeleting()) {
                // Necesitamos guardar el deleted_by antes de que Laravel ejecute el soft delete
                $model->deleted_by = $userId;
                
                // Usamos saveQuietly para evitar disparar eventos 'updating' recursivos
                // que alterarían el updated_at/updated_by incorrectamente en un borrado.
                $model->saveQuietly();
            }
        });
    }

    /**
     * Relación con el creador
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con el editor
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relación con el eliminador
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
