<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxApiLogicException;

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
