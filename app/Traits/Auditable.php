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
        // On creating: save created_by and updated_by
        static::creating(function (Model $model) {
            $userId = Auth::id();
            if ($userId) {
                $model->created_by = $userId;
                $model->updated_by = $userId;
            }
        });

        // On updating: update updated_by
        static::updating(function (Model $model) {
            $userId = Auth::id();
            if ($userId) {
                $model->updated_by = $userId;
            }
        });

        // On deleting: if SoftDelete, save deleted_by
        static::deleting(function (Model $model) {
            // Check if it uses SoftDeletes and if we have a user
            $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));
            $userId = Auth::id();

            if ($usesSoftDeletes && $userId && ! $model->isForceDeleting()) {
                // Need to save deleted_by before Laravel executes the soft delete
                $model->deleted_by = $userId;
                
                // Use saveQuietly to avoid triggering recursive 'updating' events
                // that would incorrectly alter updated_at/updated_by during a delete.
                $model->saveQuietly();
            }
        });
    }

    /**
     * Relationship with the creator
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with the editor
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship with the deleter
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
