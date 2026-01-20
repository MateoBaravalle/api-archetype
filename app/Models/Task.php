<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\BaseBuilder;
use App\Models\Traits\Filterable;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;
    use Auditable;
    use Filterable;

    /**
     * Use custom builder
     */
    public function newEloquentBuilder($query): BaseBuilder
    {
        return new BaseBuilder($query);
    }

    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks';

    /**
     * Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
        'priority',
    ];

    /**
     * Attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'due_date' => 'date',
        'priority' => 'integer',
    ];

    /**
     * Scopes for specific filters
     */
    public function scopeFilterByStatus($query, $value)
    {
        return $query->where('status', $value);
    }

    public function scopeFilterByPriority($query, $value)
    {
        return $query->where('priority', $value);
    }

    /**
     * Define columns for global search
     */
    protected function getGlobalSearchColumns(): array
    {
        return ['title', 'description'];
    }

    /**
     * Define allowed filters and their types
     */
    public function getFilters(): array
    {
        return [
            'global' => \App\Support\Query\FilterType::GLOBAL_SEARCH,
            'status' => \App\Support\Query\FilterType::EXACT,
            'priority' => \App\Support\Query\FilterType::EXACT,
        ];
    }

    /**
     * Define supported ranges
     */
    public function getRanges(): array
    {
        return [
            'date' => 'created_at',
        ];
    }

    /**
     * Default sort field
     */
    public function getDefaultSortField(): string
    {
        return 'priority';
    }
}
