<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\User;
use MaxMessenger\Bot\Models\Responses\UserAddedToChatUpdate;

/**
 * @property-read UserAddedToChatUpdate $update
 */
final class UserAddedToChatEvent extends BaseEvent
{
    use UserEventTrait;

    /**
     * @return int ID чата, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return int|null Пользователь, который добавил пользователя в чат.
     *     Может быть `null`, если пользователь присоединился к чату по ссылке.
     */
    public function getInviterId(): ?int
    {
        return $this->update->getInviterId();
    }

    /**
     * @return User Пользователь, добавленный в чат.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return bool Указывает, был ли пользователь добавлен в канал или нет.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
