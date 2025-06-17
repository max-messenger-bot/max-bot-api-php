<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions;

use InvalidArgumentException;

use function sprintf;

/**
 * Argument cannot be null.
 *
 * Exception thrown when a required argument is null.
 */
final class RequiredArgumentException extends InvalidArgumentException
{
    public function __construct(string $argument)
    {
        parent::__construct(sprintf('The $%s argument cannot be null.', $argument));
    }
}
