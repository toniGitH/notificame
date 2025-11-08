<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

final class InvalidEmailException extends InvalidValueObjectException
{
    public function __construct(string $email)
    {
        parent::__construct(__('messages.user.INVALID_EMAIL_FORMAT'));
    }
}