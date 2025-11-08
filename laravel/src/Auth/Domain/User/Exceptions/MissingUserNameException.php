<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

final class MissingUserNameException extends InvalidValueObjectException
{
    public function __construct()
    {
        parent::__construct(__('messages.user.MISSING_USER_NAME'));
    }
}