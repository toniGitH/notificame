<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

/**
 * Se lanza cuando el email no tiene un formato válido.
 */
final class InvalidEmailException extends InvalidValueObjectException
{
    public function __construct(string $email)
    {
        parent::__construct(__('messages.user.INVALID_EMAIL_FORMAT', ['email' => $email]));
    }
}