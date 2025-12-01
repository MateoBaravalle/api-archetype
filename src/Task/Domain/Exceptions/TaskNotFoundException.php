<?php

declare(strict_types=1);

namespace Src\Task\Domain\Exceptions;

use Exception;

class TaskNotFoundException extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct("Task with ID {$id} not found", 404);
    }
}


