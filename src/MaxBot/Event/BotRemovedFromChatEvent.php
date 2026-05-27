<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\BotRemovedFromChatUpdate;
use MaxMessenger\Bot\Model\Response\User;

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
     * @return int ID пользователя, удалившего бота из чата.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }

    /**
     * @return bool Указывает, что бот удалён из канала, а не из чата.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
