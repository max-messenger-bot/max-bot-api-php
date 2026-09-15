<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageEditedUpdate;

/**
 * Событие редактирования сообщения.
 *
 * Событие может относиться к объекту, который не поддерживается MAX API, — тогда сообщения в нём нет.
 * В этом случае {@see getMessage()} и методы трейта, которым нужно сообщение
 * ({@see MessageEventTrait::reply()}, {@see MessageEventTrait::deleteMessage()} и другие),
 * прерывают обработку события через {@see BaseEvent::continue()}. Проверить наличие сообщения заранее
 * можно методом {@see hasMessage()}.
 *
 * На свои действия бот события не получает.
 *
 * @property-read MessageEditedUpdate $update
 */
final class MessageEditedEvent extends BaseEvent
{
    use MessageEventTrait;

    /**
     * Отредактированное сообщение.
     *
     * Если событие относится к объекту, который не поддерживается MAX API, сообщения в нём нет
     * и обработка события прерывается через {@see BaseEvent::continue()}.
     *
     * @return Message Отредактированное сообщение.
     */
    public function getMessage(): Message
    {
        return $this->update->getMessage() ?? $this->continue();
    }

    /**
     * @return bool `true`, если событие содержит сообщение.
     */
    public function hasMessage(): bool
    {
        return $this->update->getMessage() !== null;
    }
}
