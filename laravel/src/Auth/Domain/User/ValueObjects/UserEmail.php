<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\ValueObjects;

use Src\Auth\Domain\User\Exceptions\InvalidEmailException;

/**
 * Value Object para el email del usuario.
 * Representa una dirección de correo electrónico válida.
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
        // Validación de formato email
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($value);
        }

        // Validación adicional: debe contener un punto en el dominio
        if (!str_contains($value, '.')) {
            throw new InvalidEmailException($value);
        }
    }

    public function equals(UserEmail $other): bool
    {
        return $this->value === $other->value;
    }
}