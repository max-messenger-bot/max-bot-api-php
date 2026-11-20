<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\BotAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * Событие добавления бота в чат или канал.
 *
 * @property-read BotAddedToChatUpdate $update
 */
final class BotAddedToChatEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;

    /**
     * @return int Идентификатор чата или канала, куда был добавлен бот.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return User|null Пользователь, добавивший бота в чат или канал, если он передан в обновлении.
     */
    public function getUser(): ?User
    {
        return $this->update->getUser();
    }

    /**
     * @return int|null Идентификатор пользователя, добавившего бота, если он передан в обновлении.
     */
    public function getUserId(): ?int
    {
        return $this->getUser()?->getUserId();
    }

    /**
     * @return bool Указывает, что бот добавлен в канал, а не в чат.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
