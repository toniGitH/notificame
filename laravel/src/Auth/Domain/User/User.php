<?php

declare(strict_types=1);

namespace Notifier\Auth\Domain\User;

use Notifier\Auth\Domain\User\ValueObjects\UserEmail;
use Notifier\Auth\Domain\User\ValueObjects\UserId;
use Notifier\Auth\Domain\User\ValueObjects\UserPassword;

final class User
{
    private function __construct(
        private string $name,
        private UserEmail $email,
        private UserPassword $password,
        private UserId $id
    ) {}

    public static function create(string $name, UserEmail $email, UserPassword $password): self
    {
        $id = UserId::generate();
        return new self($name, $email, $password, $id);
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): UserEmail
    {
        return $this->email;
    }

    public function password(): UserPassword
    {
        return $this->password;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id->value(),
            "name" => $this->name,
            "email" => $this->email->value(),
            "password" => $this->password->value()
        ];
    }
}
