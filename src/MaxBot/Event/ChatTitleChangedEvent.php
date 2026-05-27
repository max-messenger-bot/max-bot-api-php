<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\ChatTitleChangedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * @property-read ChatTitleChangedUpdate $update
 */
final class ChatTitleChangedEvent extends BaseEvent
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
     * @return non-empty-string Новое название.
     */
    public function getTitle(): string
    {
        return $this->update->getTitle();
    }

    /**
     * @return User Пользователь, который изменил название.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int ID пользователя, который изменил название.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }
}
