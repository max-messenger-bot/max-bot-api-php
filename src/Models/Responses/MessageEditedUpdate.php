<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Вы получите этот `update`, как только сообщение будет отредактировано.
 */
class MessageEditedUpdate extends Update
{
    /**
     * @var array{
     *     message: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Message|false $message = false;

    /**
     * @return Message Отредактированное сообщение.
     */
    public function getMessage(): Message
    {
        return $this->message === false
            ? $this->message = Message::newFromData($this->data['message'])
            : $this->message;
    }
}
