<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

/**
 * Событие неизвестного типа.
 *
 * Создаётся, когда сервер присылает обновление с типом, которому не соответствует ни один класс события.
 * Данные обновления доступны через свойство `$update`.
 */
final class UnknownEvent extends BaseEvent
{
    public function getChatId(): null
    {
        return null;
    }

    public function getUser(): null
    {
        return null;
    }

    public function getUserId(): null
    {
        return null;
    }
}
