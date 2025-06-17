<?php

namespace MaxMessenger\Bot\Exceptions\MaxBot\Events;

use MaxMessenger\Bot\Exceptions\LogicException;

/**
 * The sender is unknown.
 *
 * Exception thrown when the sender of an event cannot be identified.
 */
final class SenderUnknownException extends LogicException
{
    public function __construct()
    {
        parent::__construct('The sender is unknown.');
    }
}
