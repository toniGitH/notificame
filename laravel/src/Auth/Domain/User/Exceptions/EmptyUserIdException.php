<?php

declare(strict_types=1);

namespace Notifier\Auth\Domain\User\Exceptions;

use Exception;

final class EmptyUserIdException extends Exception
{
    public function __construct()
    {
        parent::__construct(trans('messages.user.empty_id'));
    }
}