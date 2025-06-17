<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;

/**
 * Сообщение в чате.
 *
 * @link https://dev.max.ru/docs-api/objects/Message
 */
class Message extends BaseResponseModel
{
    /**
     * @var array{
     *     sender?: array,
     *     recipient: array,
     *     timestamp: int,
     *     link?: array,
     *     body: array,
     *     stat?: array,
     *     url?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private MessageBody|false $body = false;
    private LinkedMessage|false|null $link = false;
    private Recipient|false $recipient = false;
    private User|false|null $sender = false;
    private MessageStat|false|null $stat = false;

    /**
     * @return MessageBody Содержимое сообщения. Текст + вложения.
     */
    public function getBody(): MessageBody
    {
        return $this->body === false
            ? ($this->body = MessageBody::newFromData($this->data['body']))
            : $this->body;
    }

    /**
     * @return LinkedMessage|null Пересланное или ответное сообщение.
     */
    public function getLink(): ?LinkedMessage
    {
        return $this->link === false
            ? ($this->link = LinkedMessage::newFromNullableData($this->data['link'] ?? null))
            : $this->link;
    }

    /**
     * @return Recipient Получатель сообщения. Может быть пользователем или чатом.
     */
    public function getRecipient(): Recipient
    {
        return $this->recipient === false
            ? ($this->recipient = Recipient::newFromData($this->data['recipient']))
            : $this->recipient;
    }

    /**
     * @return User|null Пользователь, отправивший сообщение.
     *     Может быть `null`, если сообщение было опубликовано от имени канала.
     */
    public function getSender(): ?User
    {
        return $this->sender === false
            ? ($this->sender = User::newFromNullableData($this->data['sender'] ?? null))
            : $this->sender;
    }

    /**
     * @return MessageStat|null Статистика сообщения. Возвращается только для постов в каналах.
     */
    public function getStat(): ?MessageStat
    {
        return $this->stat === false
            ? ($this->stat = MessageStat::newFromNullableData($this->data['stat'] ?? null))
            : $this->stat;
    }

    /**
     * @return string Текст сообщения.
     */
    public function getText(): string
    {
        return $this->getBody()->getText();
    }

    /**
     * @return DateTimeImmutable Время создания сообщения в формате Unix-time.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['timestamp']);
    }

    /**
     * @return int Время создания сообщения в формате Unix-time (Unix timestamp в миллисекундах).
     */
    public function getTimestampRaw(): int
    {
        return $this->data['timestamp'];
    }

    /**
     * @return non-empty-string|null Публичная ссылка на пост в канале (minLength: 1). Отсутствует для диалогов и групповых чатов.
     */
    public function getUrl(): ?string
    {
        return $this->data['url'] ?? null;
    }
}
