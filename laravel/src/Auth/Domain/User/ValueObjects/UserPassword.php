<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\ValueObjects;

use Src\Auth\Domain\User\Exceptions\InvalidPasswordException;

/**
 * Value Object para la contraseña del usuario.
 * Representa una contraseña segura que cumple con los requisitos de seguridad.
 * 
 * Valida TODAS las reglas y acumula TODOS los errores antes de lanzar la excepción.
 */
final class UserPassword
{
    private const MIN_LENGTH = 8;

    private function __construct(private string $value)
    {
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
        $errors = [];

        // Recolectar TODOS los errores antes de lanzar excepción
        if (strlen($value) < self::MIN_LENGTH) {
            $errors[] = __('messages.password.PASSWORD_TOO_SHORT', ['min' => self::MIN_LENGTH]);
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = __('messages.password.PASSWORD_MISSING_UPPERCASE');
        }

        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = __('messages.password.PASSWORD_MISSING_LOWERCASE');
        }

        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = __('messages.password.PASSWORD_MISSING_NUMBER');
        }

        if (!preg_match('/[@$!%*?&]/', $value)) {
            $errors[] = __('messages.password.PASSWORD_MISSING_SPECIAL');
        }

        // Si hay errores, lanzar excepción con TODOS los mensajes
        if (!empty($errors)) {
            throw new InvalidPasswordException($errors);
        }
    }

    public function equals(string $password): bool
    {
        return $this->value === $password;
    }
}