<?php

declare(strict_types=1);

namespace App\Http\Resources;

class TaskCollection extends ApiCollection
{
    /**
     * The resource class the collection contains.
     *
     * @var string
     */
    public $collects = TaskResource::class;
}
