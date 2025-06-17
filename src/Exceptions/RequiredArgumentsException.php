<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions;

use InvalidArgumentException;

/**
 * At least one argument must be non-null.
 *
 * Exception thrown when at least one argument must be provided.
 */
final class RequiredArgumentsException extends InvalidArgumentException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: 'At least one argument must be non-null.');
    }
}
