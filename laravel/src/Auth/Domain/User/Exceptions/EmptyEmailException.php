<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

final class EmptyEmailException extends InvalidValueObjectException
{
    public function __construct()
    {
        parent::__construct('messages.user.EMPTY_EMAIL');
    }
}