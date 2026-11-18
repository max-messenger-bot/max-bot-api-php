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
    /**
     * @return null Идентификатор чата недоступен для события неизвестного типа.
     */
    public function getChatId(): null
    {
        return null;
    }

    /**
     * @return null Пользователь недоступен для события неизвестного типа.
     */
    public function getUser(): null
    {
        return null;
    }

    /**
     * @return null Идентификатор пользователя недоступен для события неизвестного типа.
     */
    public function getUserId(): null
    {
        return null;
    }
}
