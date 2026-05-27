<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вы получите это событие, как только пользователь отредактирует сообщение.
 */
class MessageEditedUpdate extends Update
{
    /**
     * @var array{
     *     message?: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Message|false|null $message = false;

    /**
     * @return Message|null Отредактированное сообщение.
     */
    public function getMessage(): ?Message
    {
        return $this->message === false
            ? $this->message = Message::newFromNullableData($this->data['message'] ?? null)
            : $this->message;
    }
}
