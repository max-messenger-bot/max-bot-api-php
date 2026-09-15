<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\MessageLinkType;

class LinkedMessage extends BaseResponseModel
{
    /**
     * @var array{
     *     type: string,
     *     sender?: array,
     *     chat_id: int,
     *     message: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private MessageBody|false $message = false;
    private User|false|null $sender = false;

    /**
     * ID чата или канала, в котором опубликовано связанное сообщение.
     *
     * Для {@see isForward()} это чат-источник, для {@see isReply()} — чат самого сообщения.
     *
     * @return int|null ID чата или канала, в котором опубликовано связанное сообщение.
     *     `null`, если у бота нет доступа к чату-источнику.
     */
    public function getChatId(): ?int
    {
        /**
         * Сервер передаёт `0`, если у бота нет доступа к чату-источнику.
         *
         * @psalm-suppress RiskyTruthyFalsyComparison Поле объявлено обязательным, но проверяем и его отсутствие.
         */
        return $this->data['chat_id'] ?? null ?: null;
    }

    /**
     * @return MessageBody Информация о связанном сообщении.
     */
    public function getMessage(): MessageBody
    {
        return $this->message === false
            ? ($this->message = MessageBody::newFromData($this->data['message']))
            : $this->message;
    }

    /**
     * @return User|null Пользователь или бот, отправивший сообщение.
     *     Может быть `null`, если сообщение опубликовано от имени канала.
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
