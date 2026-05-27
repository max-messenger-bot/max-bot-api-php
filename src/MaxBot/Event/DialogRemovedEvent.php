<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\DialogRemovedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * @property-read DialogRemovedUpdate $update
 */
final class DialogRemovedEvent extends BaseEvent
{
    /**
     * @return int ID чата, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return User Пользователь, который удалил чат.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int ID пользователя, который удалил чат.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }

    /**
     * @return string|null Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }
}
