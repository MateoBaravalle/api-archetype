<?php

declare(strict_types=1);

namespace App\Http\Resources;

class TaskCollection extends ApiCollection
{
    /**
     * La clase de recurso que la colección contiene.
     *
     * @var string
     */
    public $collects = TaskResource::class;
}
