<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вы получите это событие, как только пользователь отредактирует сообщение.
 *
 * Событие может относиться к объекту, который не поддерживается MAX API.
 * В этом случае {@see getMessage()} вернёт `null`.
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
     *     `null`, если событие относится к объекту, который не поддерживается MAX API.
     */
    public function getMessage(): ?Message
    {
        return $this->message === false
            ? $this->message = Message::newFromNullableData($this->data['message'] ?? null)
            : $this->message;
    }
}
