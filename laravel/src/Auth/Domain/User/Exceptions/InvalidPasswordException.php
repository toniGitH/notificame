<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

/**
 * Se lanza cuando la contraseña no cumple con uno o más requisitos de seguridad.
 * Puede contener múltiples mensajes de error.
 */
final class InvalidPasswordException extends InvalidValueObjectException
{
    private array $errors = [];

    /**
     * @param array $errors Array de mensajes de error
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        
        // El mensaje principal es el primero del array
        parent::__construct($errors[0] ?? 'Invalid password');
    }

    /**
     * Obtiene todos los errores de validación
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Verifica si hay múltiples errores
     */
    public function hasMultipleErrors(): bool
    {
        return count($this->errors) > 1;
    }
}