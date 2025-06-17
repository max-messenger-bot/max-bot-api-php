<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\BotAddedToChatUpdate;
use MaxMessenger\Bot\Models\Responses\User;

/**
 * @property-read BotAddedToChatUpdate $update
 */
final class BotAddedToChatEvent extends BaseEvent
{
    use UserEventTrait;

    /**
     * @return int ID чата, куда был добавлен бот.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return User Пользователь, добавивший бота в чат.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return bool Указывает, был ли бот добавлен в канал или нет.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
