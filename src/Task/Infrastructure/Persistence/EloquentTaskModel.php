<?php

declare(strict_types=1);

namespace Src\Task\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EloquentTaskModel extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     */
    protected $table = 'tasks';

    /**
     * Los atributos que son asignables masivamente.
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
        'priority',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'due_date' => 'date',
        'priority' => 'integer',
    ];
}


