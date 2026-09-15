<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxApiLogicException;

/**
 * Идентификатор чата отсутствует.
 *
 * Исключение выбрасывается, когда у события нет чата или он не был передан.
 */
final class ChatIdMissingException extends MaxApiLogicException
{
    public function __construct()
    {
        parent::__construct('Chat id missing.');
    }
}
