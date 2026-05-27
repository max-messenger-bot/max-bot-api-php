<?php

namespace MaxMessenger\Bot\Exception\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxApiLogicException;

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
