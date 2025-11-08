<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\ValueObjects;

use Src\Auth\Domain\User\Exceptions\InvalidUserNameException;

/**
 * Value Object para el nombre de usuario.
 * Representa un nombre válido que cumple con las reglas de negocio.
 *
 * REGLAS DE VALIDACIÓN:
 * - No puede estar vacío
 * - Longitud mínima: 3 caracteres
 * - Longitud máxima: 50 caracteres
 */
final class UserName
{
    private string $value;

    private function __construct(string $value)
    {
        $this->ensureIsValidUserName($value);
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        $value = trim($value);
        
        // Validación completa en un solo lugar
        if (empty($value) || mb_strlen($value) < 3 || mb_strlen($value) > 50) {
            throw new InvalidUserNameException();
        }

        return new self($value);
    }

    private function ensureIsValidUserName(string $value): void
    {
        // Ya validado en fromString, pero por si acaso
        $length = mb_strlen($value);

        if (empty($value) || $length < 3 || $length > 50) {
            throw new InvalidUserNameException();
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UserName $other): bool
    {
        return $this->value === $other->value();
    }
}