<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\BotRemovedFromChatUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * Событие удаления бота из чата или канала.
 *
 * @property-read BotRemovedFromChatUpdate $update
 */
final class BotRemovedFromChatEvent extends BaseEvent
{
    use SendMessageToUserTrait;

    /**
     * @return int Идентификатор чата или канала, откуда был удалён бот.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return User|null Пользователь, удаливший бота из чата или канала, если он передан в обновлении.
     */
    public function getUser(): ?User
    {
        return $this->update->getUser();
    }

    /**
     * @return int|null Идентификатор пользователя, удалившего бота, если он передан в обновлении.
     */
    public function getUserId(): ?int
    {
        return $this->getUser()?->getUserId();
    }

    /**
     * @return bool Указывает, что бот удалён из канала, а не из чата.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
