<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\DialogRemovedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * Событие удаления диалога с ботом.
 *
 * Вместе с этим событием приходит {@see BotStoppedEvent} — при удалении диалога бот останавливается автоматически.
 *
 * @property-read DialogRemovedUpdate $update
 */
final class DialogRemovedEvent extends BaseEvent
{
    /**
     * @return int Идентификатор диалога, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return User Пользователь, который удалил диалог.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int Идентификатор пользователя, который удалил диалог.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }
}
