<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\ValueObjects;

use Src\Auth\Domain\User\Exceptions\InvalidUserNameException;
use Src\Auth\Domain\User\Exceptions\MissingUserNameException;

/**
 * Value Object para el nombre de usuario.
 * Representa un nombre válido que cumple con las reglas de negocio.
 * 
 * REGLAS DE VALIDACIÓN:
 * - No puede estar vacío
 * - Longitud mínima: 2 caracteres
 * - Longitud máxima: 100 caracteres
 * - Solo caracteres alfanuméricos, espacios, guiones y guiones bajos
 */
final class UserName
{
    private function __construct(private string $value)
    {
        $this->ensureIsValidUserName($value);
    }

    /**
     * Crea un UserName desde un string.
     * 
     * @param string $value Nombre del usuario
     * @return self
     * @throws InvalidUserNameException|MissingUserNameException
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Obtiene el valor del nombre.
     * 
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Valida que el nombre sea válido.
     * 
     * @param string $value
     * @throws InvalidUserNameException|MissingUserNameException
     */
    private function ensureIsValidUserName(string $value): void
    {
        $trimmedValue = trim($value);
        
        if (empty($trimmedValue)) {
            throw new MissingUserNameException();
        }
        
        $errors = [];
        
        // Longitud mínima
        if (strlen($trimmedValue) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres';
        }
        
        // Longitud máxima
        if (strlen($trimmedValue) > 100) {
            $errors[] = 'El nombre no puede exceder 100 caracteres';
        }
        
        // Caracteres permitidos (solo alfanuméricos, espacios, guiones, guiones bajos)
        if (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $trimmedValue)) {
            $errors[] = 'El nombre solo puede contener letras, números, espacios, guiones y guiones bajos';
        }
        
        // Si hay errores, lanzar excepción con TODOS los mensajes
        if (!empty($errors)) {
            throw new InvalidUserNameException($errors);
        }
    }

    /**
     * Compara si dos UserName son iguales.
     * 
     * @param UserName $other
     * @return bool
     */
    public function equals(UserName $other): bool
    {
        return $this->value === $other->value;
    }
}