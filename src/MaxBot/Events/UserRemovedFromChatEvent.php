<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\User;
use MaxMessenger\Bot\Models\Responses\UserRemovedFromChatUpdate;

/**
 * @property-read UserRemovedFromChatUpdate $update
 */
final class UserRemovedFromChatEvent extends BaseEvent
{
    use UserEventTrait;

    /**
     * @return int|null Администратор, который удалил пользователя из чата.
     *     Может быть `null`, если пользователь покинул чат сам.
     */
    public function getAdminId(): ?int
    {
        return $this->update->getAdminId();
    }

    /**
     * @return int ID чата, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return User Пользователь, удалённый из чата.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return bool Указывает, был ли пользователь удалён из канала или нет.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
