<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\BotStoppedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * @property-read BotStoppedUpdate $update
 */
final class BotStoppedEvent extends BaseEvent
{
    /**
     * @return int ID диалога, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return User Пользователь, который остановил бота.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int ID пользователя, который остановил бота.
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
