<?php

declare(strict_types=1);

namespace Src\Auth\Domain\Events;

use Src\Auth\Domain\Entities\User;

class UserRegistered
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function user(): User
    {
        return $this->user;
    }
}


