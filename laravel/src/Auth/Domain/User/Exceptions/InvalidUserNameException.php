<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

/**
 * Se lanza cuando el nombre de usuario no cumple con las reglas de negocio.
 * Puede contener múltiples mensajes de error.
 * 
 * REGLAS ACTUALES:
 * - Longitud mínima: 2 caracteres
 * - Longitud máxima: 100 caracteres
 * - Solo caracteres alfanuméricos, espacios, guiones y guiones bajos
 */
final class InvalidUserNameException extends InvalidValueObjectException
{
    private array $errors = [];

    /**
     * @param array $errors Array de mensajes de error
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        
        // El mensaje principal es el primero del array
        parent::__construct($errors[0] ?? 'Invalid user name');
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