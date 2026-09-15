<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\BotAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * Событие добавления бота в чат или канал.
 *
 * @property-read BotAddedToChatUpdate $update
 * @psalm-suppress DeprecatedTrait {@see UserEventTrait} подключён для совместимости.
 */
final class BotAddedToChatEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;
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
     * @return int ID пользователя, добавившего бота в чат.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }

    /**
     * @return bool Указывает, что бот добавлен в канал, а не в чат.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
