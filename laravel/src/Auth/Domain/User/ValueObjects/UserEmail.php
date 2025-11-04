<?php

declare(strict_types=1);

namespace Notifier\Auth\Domain\User\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para el email del usuario
 */
final class UserEmail
{
    private function __construct(private string $value)
    {
        $this->ensureIsValidEmail($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    private function ensureIsValidEmail(string $value): void
    {
        if (!str_contains($value, '.')) {
            throw new InvalidArgumentException('Email must contain a dot.');
        }

        if (!str_contains($value, '@')) {
            throw new InvalidArgumentException('Email must contain an "@" symbol.');
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email format is invalid.');
        }
    }

    public function equals(UserEmail $other): bool
    {
        return $this->value === $other->value;
    }
}
