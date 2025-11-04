<?php

declare(strict_types=1);

namespace Notifier\Auth\Application\Ports\Out;

use Notifier\Auth\Domain\User\User;
use Notifier\Auth\Domain\User\ValueObjects\UserEmail;

interface UserRepository
{
    /**
     * Guarda un nuevo usuario
     */
    public function save(User $user): void;

    /**
     * Verifica si existe un usuario con el email dado
     */
    public function exists(UserEmail $email): bool;
}