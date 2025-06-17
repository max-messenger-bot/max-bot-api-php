<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\Message;
use MaxMessenger\Bot\Models\Responses\MessageCreatedUpdate;

/**
 * @property-read MessageCreatedUpdate $update
 */
final class MessageCreatedEvent extends BaseEvent
{
    use MessageEventTrait;

    /**
     * @return Message Новое созданное сообщение.
     */
    public function getMessage(): Message
    {
        return $this->update->getMessage();
    }

    /**
     * @return string|null Текущий язык пользователя в формате IETF BCP 47. Доступно только в диалогах.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }
}
