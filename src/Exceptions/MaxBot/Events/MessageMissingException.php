<?php

namespace MaxMessenger\Bot\Exceptions\MaxBot\Events;

use MaxMessenger\Bot\Exceptions\MaxApiLogicException;

/**
 * Сообщение отсутствует.
 *
 * Исключение выбрасывается, когда сообщение отсутствует в событии.
 */
final class MessageMissingException extends MaxApiLogicException
{
    public function __construct()
    {
        parent::__construct('Message missing.');
    }
}
