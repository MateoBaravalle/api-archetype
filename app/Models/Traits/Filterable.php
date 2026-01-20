<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Support\Query\FilterType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Filterable
{
    /**
     * Applies filters and sorting to a query
     */
    public function scopeFilterAndSort(Builder $query, array $params): Builder
    {
        // Defaults
        $params['sort_by'] ??= method_exists($this, 'getDefaultSortField') ? $this->getDefaultSortField() : 'created_at';
        $params['sort_order'] ??= method_exists($this, 'getDefaultSortOrder') ? $this->getDefaultSortOrder() : 'desc';

        // 1. Apply Defined Filters
        $this->applyDefinedFilters($query, $params);

        // 2. Apply Range Filters
        $this->applyRangeFilters($query, $params);

        // 3. Apply Sorting Logic
        $this->applySortingLogic($query, $params);

        return $query;
    }

    protected function applyDefinedFilters(Builder $query, array $params): void
    {
        $definitions = method_exists($this, 'getFilters') ? $this->getFilters() : [];

        foreach ($definitions as $paramName => $type) {
            if (! isset($params[$paramName]) || $params[$paramName] === '') {
                continue;
            }

            $value = $params[$paramName];

            // Normalization
            if ($value === 'true') $value = true;
            if ($value === 'false') $value = false;

            // Custom Scope
            $scopeName = 'filterBy' . Str::studly($paramName);
            if (method_exists($this, 'scope' . ucfirst($scopeName))) {
                $query->$scopeName($value);
                continue;
            }

            // Apply logic according to type
            match ($type) {
                FilterType::EXACT => $query->where($paramName, $value),
                FilterType::PARTIAL => $query->where($paramName, 'like', '%' . $value . '%'),
                FilterType::IN => $query->whereIn($paramName, (array) $value),
                FilterType::GLOBAL_SEARCH => $this->applyGlobalSearch($query, $value),
                FilterType::SIMPLE_SEARCH => $this->applySimpleSearch($query, $value),
                default => $query->where($paramName, $value),
            };
        }
    }

    protected function applyRangeFilters(Builder $query, array $params): void
    {
        $ranges = method_exists($this, 'getRanges') ? $this->getRanges() : [];

        foreach ($ranges as $prefix => $field) {
            // Extract min/max or start/end from flat params
            $start = $params["{$prefix}_start"] ?? $params["start_{$prefix}"] ?? $params["{$prefix}_min"] ?? $params["min_{$prefix}"] ?? null;
            $end = $params["{$prefix}_end"] ?? $params["end_{$prefix}"] ?? $params["{$prefix}_max"] ?? $params["max_{$prefix}"] ?? null;

            if ($start || $end) {
                $this->applyRangeFilter($query, $field, [
                    'start' => $start,
                    'end' => $end
                ]);
            }
        }
    }

    protected function applySortingLogic(Builder $query, array $params): void
    {
        $defaultSort = method_exists($this, 'getDefaultSortField') ? $this->getDefaultSortField() : 'created_at';
        $sortBy = $params['sort_by'] ?? $defaultSort;
        $sortOrder = $params['sort_order'] ?? 'desc';

        $scopeName = 'sortBy' . ucfirst($sortBy);
        if (method_exists($this, 'scope' . $scopeName)) {
            $query->$scopeName($sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
    }

    /**
     * Applies a range filter to the query
     */
    protected function applyRangeFilter(Builder $query, string $field, array $range): void
    {
        $start = $range['start'] ?? null;
        $end = $range['end'] ?? null;

        $query->where(function ($q) use ($field, $start, $end) {
            if ($start !== null) {
                if (Str::endsWith($field, ['_at', '_date'])) {
                     $q->whereDate($field, '>=', $start);
                } else {
                     $q->where($field, '>=', $start);
                }
            }

            if ($end !== null) {
                if (Str::endsWith($field, ['_at', '_date'])) {
                     $q->whereDate($field, '<=', $end);
                } else {
                     $q->where($field, '<=', $end);
                }
            }
        });
    }

    /**
     * Applies a global search to the query
     */
    protected function applyGlobalSearch(Builder $query, string $value): Builder
    {
        $value = strtolower($value);

        $query->where(function ($q) use ($value) {
            // Apply search on text columns
            foreach ($this->getGlobalSearchColumns() as $column) {
                $q->orWhere($column, 'like', "%{$value}%");
            }

            // Apply search on relations
            $this->applyGlobalSearchToRelations($q, $value);
        });

        return $query;
    }

    /**
     * Returns the text columns that can be searched globally
     */
    protected function getGlobalSearchColumns(): array
    {
        return [];
    }

    /**
     * Applies global search to defined relations
     */
    protected function applyGlobalSearchToRelations(Builder $query, string $value): void
    {
        foreach ($this->getGlobalSearchRelations() as $relation => $columns) {
            $query->orWhereHas($relation, function ($q) use ($columns, $value) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$value}%");
                }
            });
        }
    }

    /**
     * Returns the relations and their columns for global search
     */
    protected function getGlobalSearchRelations(): array
    {
        return [];
    }

    /**
     * Applies a simple search to the query (only returns ID and name field)
     */
    protected function applySimpleSearch(Builder $query, string $value): Builder
    {
        $value = strtolower($value);
        $nameField = $this->getSimpleSearchNameField();
        $selectFields = $this->getSimpleSearchSelectFields();

        // Select configured fields
        $query->select($selectFields);

        // Apply filter on the name field
        $query->where($nameField, 'like', "%{$value}%");

        return $query;
    }

    /**
     * Returns the field that represents the "name" for simple searches
     */
    protected function getSimpleSearchNameField(): string
    {
        return 'name';
    }

    /**
     * Returns the fields that should be selected in simple searches
     */
    protected function getSimpleSearchSelectFields(): array
    {
        return ['id', $this->getSimpleSearchNameField()];
    }
}
