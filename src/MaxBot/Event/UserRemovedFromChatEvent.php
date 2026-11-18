<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\User;
use MaxMessenger\Bot\Model\Response\UserRemovedFromChatUpdate;

/**
 * Событие удаления пользователя из чата или канала.
 *
 * Приходит, когда пользователя удалили или он покинул чат или канал.
 * Бот должен быть администратором с правом {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @property-read UserRemovedFromChatUpdate $update
 */
final class UserRemovedFromChatEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;

    /**
     * @return int|null Идентификатор администратора, который удалил пользователя из чата или канала.
     *     Может быть `null`, если пользователь покинул чат или канал сам.
     */
    public function getAdminId(): ?int
    {
        return $this->update->getAdminId();
    }

    /**
     * @return int Идентификатор чата или канала, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return User Пользователь, удалённый из чата или канала.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int Идентификатор пользователя, удалённого из чата или канала.
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
