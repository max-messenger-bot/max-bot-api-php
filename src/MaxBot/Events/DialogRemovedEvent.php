<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\DialogRemovedUpdate;
use MaxMessenger\Bot\Models\Responses\User;

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
     * @return string|null Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }
}
