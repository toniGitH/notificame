<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

use Src\Shared\Domain\Exceptions\DomainException;

final class EmailAlreadyExistsException extends DomainException
{
    public function __construct(string $email)
    {
        //parent::__construct(__('messages.user.EMAIL_ALREADY_EXISTS', ['email' => $email]));
        //parent::__construct('messages.user.EMAIL_ALREADY_EXISTS', ['email' => $email]);
        parent::__construct('messages.user.EMAIL_ALREADY_EXISTS');
    }
}