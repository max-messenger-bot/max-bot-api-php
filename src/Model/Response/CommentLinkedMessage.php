<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\MessageLinkType;

/**
 * Комментарий, на который получен ответ.
 */
class CommentLinkedMessage extends BaseResponseModel
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
    private CommentMessageBody|false $message = false;
    private User|false|null $sender = false;

    /**
     * @return int ID канала, в котором оставлен комментарий.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return CommentMessageBody Информация о комментарии.
     */
    public function getMessage(): CommentMessageBody
    {
        return $this->message === false
            ? ($this->message = CommentMessageBody::newFromData($this->data['message']))
            : $this->message;
    }

    /**
     * @return User|null Пользователь или бот, отправивший комментарий.
     *     Может быть `null`, если комментарий опубликован от имени канала.
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
