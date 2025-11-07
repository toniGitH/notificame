<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

use Src\Shared\Domain\Exceptions\DomainException;

/**
 * Se lanza cuando se intenta registrar un email que ya existe en el sistema.
 * Esta es una excepción de regla de negocio, no de validación de value object.
 */
final class EmailAlreadyExistsException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(__('messages.user.EMAIL_ALREADY_EXISTS', ['email' => $email]));
    }
}