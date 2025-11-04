<?php

declare(strict_types=1);

namespace Notifier\Auth\Domain\User\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para la contraseña del usuario
 */
final class UserPassword
{
    private const MIN_LENGTH = 8;

    private function __construct(
        private string $value
    ) {
        $this->ensureIsValidPassword($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    private function ensureIsValidPassword(string $value): void
    {
        if (strlen($value) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Password must be at least %d characters long', self::MIN_LENGTH)
            );
        }

        if (!preg_match('/[A-Z]/', $value)) {
            throw new InvalidArgumentException('Password must contain at least one uppercase letter');
        }

        if (!preg_match('/[a-z]/', $value)) {
            throw new InvalidArgumentException('Password must contain at least one lowercase letter');
        }

        if (!preg_match('/[0-9]/', $value)) {
            throw new InvalidArgumentException('Password must contain at least one number');
        }

        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            throw new InvalidArgumentException('Password must contain at least one special character');
        }
    }

    /**
     * Nota: Este método ahora solo verifica si la contraseña proporcionada coincide con el valor sin hashear
     * Para comparar con un hash, usar password_verify directamente
     */
    public function equals(string $password): bool
    {
        return $this->value === $password;
    }
}