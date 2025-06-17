<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\MaxBot\Update;

use Throwable;

/**
 * Bad request.
 *
 * Exception thrown when a request containing update has an incorrect or invalid format.
 */
final class BadRequestException extends UpdateRequestException
{
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}
