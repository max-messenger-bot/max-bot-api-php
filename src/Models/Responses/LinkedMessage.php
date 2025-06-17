<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\MessageLinkType;

class LinkedMessage extends BaseResponseModel
{
    /**
     * @var array{
     *     type: string,
     *     sender?: array,
     *     chat_id?: int,
     *     message: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private MessageBody|false $message = false;
    private User|false|null $sender = false;

    /**
     * @return int|null Чат, в котором сообщение было изначально опубликовано. Только для пересланных сообщений.
     */
    public function getChatId(): ?int
    {
        return $this->data['chat_id'] ?? null;
    }

    /**
     * @return MessageBody
     */
    public function getMessage(): MessageBody
    {
        return $this->message === false
            ? $this->message = MessageBody::newFromData($this->data['message'])
            : $this->message;
    }

    /**
     * @return User|null Пользователь, отправивший сообщение.
     */
    public function getSender(): ?User
    {
        return $this->sender === false
            ? ($this->sender = User::newFromNullableData($this->data['sender'] ?? null))
            : $this->sender;
    }

    /**
     * @return MessageLinkType|null Тип связанного сообщения.
     */
    public function getType(): ?MessageLinkType
    {
        return MessageLinkType::tryFrom($this->data['type']);
    }

    /**
     * @return string Тип связанного сообщения.
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    public function isForward(): bool
    {
        return $this->data['type'] === MessageLinkType::Forward->value;
    }

    public function isReply(): bool
    {
        return $this->data['type'] === MessageLinkType::Reply->value;
    }
}
