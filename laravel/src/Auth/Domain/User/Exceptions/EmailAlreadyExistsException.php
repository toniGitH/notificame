<?php

declare(strict_types=1);

namespace Notifier\Auth\Domain\User\Exceptions;

use Exception;

final class EmailAlreadyExistsException extends Exception
{
    public function __construct()
    {
        // Solo un mensaje o código interno, sin dependencia de Laravel
        parent::__construct('EMAIL_ALREADY_EXISTS');
    }
}
