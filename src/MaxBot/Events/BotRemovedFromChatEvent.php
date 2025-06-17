<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\BotRemovedFromChatUpdate;
use MaxMessenger\Bot\Models\Responses\User;

/**
 * @property-read BotRemovedFromChatUpdate $update
 */
final class BotRemovedFromChatEvent extends BaseEvent
{
    use UserEventTrait;

    /**
     * @return int ID чата, откуда был удалён бот.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return User Пользователь, удаливший бота из чата.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return bool Указывает, был ли бот удалён из канала или нет.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
