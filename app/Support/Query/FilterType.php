<?php

declare(strict_types=1);

namespace App\Support\Query;

enum FilterType: string
{
    case EXACT = 'exact';
    case PARTIAL = 'partial';
    case IN = 'in';
    case RANGE = 'range';
    case SIMPLE_SEARCH = 'simple_search';
    case GLOBAL_SEARCH = 'global_search';
}