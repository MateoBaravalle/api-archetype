<?php

declare(strict_types=1);

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseBuilder extends Builder
{
    /**
     * Paginate results using safe parameters
     */
    public function paginateFromParams(array $params): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 10;
        $page = $params['page'] ?? 1;

        return $this->paginate($perPage, ['*'], 'page', $page);
    }
}
