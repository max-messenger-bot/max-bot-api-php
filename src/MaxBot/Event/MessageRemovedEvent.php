<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\MessageRemovedUpdate;

/**
 * Событие удаления сообщения.
 *
 * На свои действия бот события не получает.
 *
 * @property-read MessageRemovedUpdate $update
 */
final class MessageRemovedEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;

    /**
     * @return int ID чата, где сообщение было удалено.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return non-empty-string ID удалённого сообщения.
     */
    public function getMessageId(): string
    {
        return $this->update->getMessageId();
    }

    public function getUser(): null
    {
        return null;
    }

    /**
     * @return int ID пользователя, удалившего сообщение.
     */
    public function getUserId(): int
    {
        return $this->update->getUserId();
    }

}
