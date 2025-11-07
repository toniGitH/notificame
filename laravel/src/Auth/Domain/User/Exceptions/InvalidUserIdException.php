<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

/**
 * Se lanza cuando el ID de usuario no es válido.
 * Esto incluye: ID vacío, formato inválido, no es UUID v4, etc.
 */
final class InvalidUserIdException extends InvalidValueObjectException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}