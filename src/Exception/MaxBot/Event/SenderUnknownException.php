<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxApiLogicException;

/**
 * Отправитель неизвестен.
 *
 * Исключение выбрасывается, когда отправителя сообщения или комментария определить нельзя (отправлено от имени канала).
 */
final class SenderUnknownException extends MaxApiLogicException
{
    public function __construct()
    {
        parent::__construct('The sender is unknown.');
    }
}
