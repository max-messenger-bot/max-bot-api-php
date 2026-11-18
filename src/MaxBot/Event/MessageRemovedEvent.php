<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\MessageRemovedUpdate;

/**
 * Событие удаления сообщения.
 *
 * В групповом чате бот получает событие, только если он администратор с правом
 * {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @property-read MessageRemovedUpdate $update
 */
final class MessageRemovedEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;

    /**
     * @return int Идентификатор чата, где сообщение было удалено.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return non-empty-string Идентификатор удалённого сообщения.
     */
    public function getMessageId(): string
    {
        return $this->update->getMessageId();
    }

    /**
     * @return null У события нет пользователя.
     */
    public function getUser(): null
    {
        return null;
    }

    /**
     * @return int Идентификатор пользователя, удалившего сообщение.
     */
    public function getUserId(): int
    {
        return $this->update->getUserId();
    }
}
