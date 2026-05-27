<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\User;
use MaxMessenger\Bot\Model\Response\UserAddedToChatUpdate;

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
     * @return int|null Пользователь, который добавил нового пользователя в чат.
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
     * @return int ID пользователя, добавленного в чат.
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
