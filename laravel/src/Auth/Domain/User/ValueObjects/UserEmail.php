<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\ValueObjects;

use Src\Auth\Domain\User\Exceptions\InvalidEmailException;

final class UserEmail
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim($value);
        $this->ensureIsValidEmail($value);
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function ensureIsValidEmail(string $email): void
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($email);
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}