<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use DateTimeImmutable;

/**
 * Комментарий к посту в канале.
 *
 * В отличие от сообщения в чате или поста в канале не содержит вложений `attachments`
 * и не поддерживает пересылку комментариев (`link.type = forward`).
 *
 * @link https://dev.max.ru/docs-api/objects/CommentMessage
 */
class CommentMessage extends BaseResponseModel
{
    /**
     * @var array{
     *     sender?: array,
     *     recipient: array,
     *     timestamp: non-negative-int,
     *     link?: array,
     *     body: array,
     *     url?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private CommentMessageBody|false $body = false;
    private CommentLinkedMessage|false|null $link = false;
    private Recipient|false $recipient = false;
    private User|false|null $sender = false;

    /**
     * @return CommentMessageBody Информация о комментарии.
     */
    public function getBody(): CommentMessageBody
    {
        return $this->body === false
            ? ($this->body = CommentMessageBody::newFromData($this->data['body']))
            : $this->body;
    }

    /**
     * @return CommentLinkedMessage|null Комментарий, на который получен ответ.
     */
    public function getLink(): ?CommentLinkedMessage
    {
        return $this->link === false
            ? ($this->link = CommentLinkedMessage::newFromNullableData($this->data['link'] ?? null))
            : $this->link;
    }

    /**
     * @return Recipient Получатель комментария: канал.
     */
    public function getRecipient(): Recipient
    {
        return $this->recipient === false
            ? ($this->recipient = Recipient::newFromData($this->data['recipient']))
            : $this->recipient;
    }

    /**
     * @return User|null Пользователь, отправивший комментарий.
     *     Может быть `null`, если комментарий опубликован от имени канала.
     */
    public function getSender(): ?User
    {
        return $this->sender === false
            ? ($this->sender = User::newFromNullableData($this->data['sender'] ?? null))
            : $this->sender;
    }

    /**
     * @return non-empty-string Текст комментария.
     */
    public function getText(): string
    {
        return $this->getBody()->getText();
    }

    /**
     * @return DateTimeImmutable Время создания комментария.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['timestamp']);
    }

    /**
     * @return non-negative-int Время создания комментария (Unix-время в миллисекундах).
     */
    public function getTimestampRaw(): int
    {
        return $this->data['timestamp'];
    }

    /**
     * @return non-empty-string|null Публичная ссылка на комментарий в канале.
     */
    public function getUrl(): ?string
    {
        return $this->data['url'] ?? null;
    }
}
