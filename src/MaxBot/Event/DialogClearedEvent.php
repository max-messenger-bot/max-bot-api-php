<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\DialogClearedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * @property-read DialogClearedUpdate $update
 */
final class DialogClearedEvent extends BaseEvent
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
     * @return User Пользователь, который очистил историю диалога.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int ID пользователя, который очистил историю диалога.
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
