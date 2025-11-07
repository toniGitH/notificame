<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

use Src\Shared\Domain\Exceptions\DomainException;

/**
 * Se lanza cuando el nombre de usuario está vacío o falta.
 * Esta excepción es de la entidad User, no de un value object.
 */
final class MissingUserNameException extends DomainException
{
    public function __construct()
    {
        parent::__construct(__('messages.user.MISSING_USER_NAME'));
    }
}