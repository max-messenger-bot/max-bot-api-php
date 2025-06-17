<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\MaxBot\Update;

/**
 * Invalid Secret.
 *
 * Exception thrown when the provided secret is invalid or incorrect.
 */
final class InvalidSecretException extends UpdateRequestException
{
    public function __construct()
    {
        parent::__construct('Invalid Secret.', 403);
    }
}
