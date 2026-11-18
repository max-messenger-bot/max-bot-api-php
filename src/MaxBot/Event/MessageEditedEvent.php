<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageEditedUpdate;

/**
 * Событие редактирования сообщения.
 *
 * В групповом чате бот получает событие, только если он администратор с правом
 * {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @property-read MessageEditedUpdate $update
 */
final class MessageEditedEvent extends BaseEvent
{
    use MessageEventTrait;

    /**
     * @return Message Отредактированное сообщение.
     */
    public function getMessage(): Message
    {
        return $this->update->getMessage();
    }
}
