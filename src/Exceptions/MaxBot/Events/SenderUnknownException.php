<?php

namespace MaxMessenger\Bot\Exceptions\MaxBot\Events;

use MaxMessenger\Bot\Exceptions\MaxApiLogicException;

/**
 * The sender is unknown.
 *
 * Exception thrown when the sender of an event cannot be identified.
 */
final class SenderUnknownException extends MaxApiLogicException
{
    public function __construct()
    {
        parent::__construct('The sender is unknown.');
    }
}
