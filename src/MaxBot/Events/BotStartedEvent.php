<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\BotStartedUpdate;
use MaxMessenger\Bot\Models\Responses\User;

/**
 * @property-read BotStartedUpdate $update
 */
final class BotStartedEvent extends BaseEvent
{
    use UserEventTrait;

    /**
     * @return int ID диалога, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return non-empty-string|null Дополнительные данные из дип-линков, переданные при запуске бота
     *     (minLength: 1, maxLength: 128).
     */
    public function getPayload(): ?string
    {
        return $this->update->getPayload();
    }

    /**
     * @return User Пользователь, который нажал кнопку 'Start'.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int Идентификатор пользователя, который нажал кнопку 'Start'.
     */
    public function getUserId(): int
    {
        return $this->update->getUserId();
    }

    /**
     * @return string|null Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }
}
