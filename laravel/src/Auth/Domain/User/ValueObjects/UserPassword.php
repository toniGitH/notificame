<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\ValueObjects;

use Src\Auth\Domain\User\Exceptions\InvalidPasswordException;

final class UserPassword
{
    private const REGEX = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/';

    private string $value;

    private function __construct(string $value)
    {
        $this->ensureIsValidPassword($value);
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function ensureIsValidPassword(string $password): void
    {
        if (strlen($password) < 8 || !preg_match(self::REGEX, $password)) {
            throw new InvalidPasswordException();
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}