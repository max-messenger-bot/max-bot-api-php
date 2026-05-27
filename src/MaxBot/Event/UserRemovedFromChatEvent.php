<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\User;
use MaxMessenger\Bot\Model\Response\UserRemovedFromChatUpdate;

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
     * @return int ID пользователя, удалённого из чата.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }

    /**
     * @return bool Указывает, что пользователь удалён из канала, а не из чата.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
