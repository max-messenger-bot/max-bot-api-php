<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions;

use InvalidArgumentException;

final class RequireArgumentsException extends InvalidArgumentException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: 'At least one argument must be non-null.');
    }
}
