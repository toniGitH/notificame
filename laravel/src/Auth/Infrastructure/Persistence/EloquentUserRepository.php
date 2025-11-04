<?php

declare(strict_types=1);

namespace Notifier\Auth\Infrastructure\Persistence;

use Notifier\Auth\Application\Ports\Out\UserRepository;
use Notifier\Auth\Domain\User\User;
use Notifier\Auth\Domain\User\ValueObjects\UserEmail;
use Notifier\Auth\Domain\User\Exceptions\EmptyUserIdException;
use App\Models\User as EloquentUser;

final class EloquentUserRepository implements UserRepository
{
    public function save(User $user): void
    {
        $eloquentUser = new EloquentUser();
        $eloquentUser->id = $user->id()->value();
        $eloquentUser->name = $user->name();
        $eloquentUser->email = $user->email()->value();
        $eloquentUser->password = $user->password()->value(); // Usamos el valor sin hashear
        $eloquentUser->save();
    }

    public function exists(UserEmail $email): bool
    {
        return EloquentUser::where('email', $email->value())->exists();
    }
}