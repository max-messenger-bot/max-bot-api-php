<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\User;
use MaxMessenger\Bot\Model\Response\UserAddedToChatUpdate;

/**
 * Событие добавления пользователя в чат или канал.
 *
 * Приходит, когда пользователя добавили или он перешёл по ссылке.
 * Бот должен быть администратором с правом {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @property-read UserAddedToChatUpdate $update
 */
final class UserAddedToChatEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;

    /**
     * @return int Идентификатор чата или канала, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return int|null Идентификатор пользователя, который добавил нового пользователя в чат или канал.
     *     Может быть `null`, если пользователь присоединился по ссылке.
     */
    public function getInviterId(): ?int
    {
        return $this->update->getInviterId();
    }

    /**
     * @return User Пользователь, добавленный в чат или канал.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int Идентификатор пользователя, добавленного в чат или канал.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }

    /**
     * @return bool Указывает, что пользователь добавлен в канал, а не в чат.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
