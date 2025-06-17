<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;

/**
 * Message in chat.
 *
 * @api
 */
class Message extends BaseResponseModel
{
    /**
     * @var array{
     *     sender?: array|null,
     *     recipient: array,
     *     timestamp: int,
     *     link?: array|null,
     *     body?: array|null,
     *     stat?: array|null,
     *     url?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private MessageBody|false|null $body = false;
    private LinkedMessage|false|null $link = false;
    private Recipient|false $recipient = false;
    private User|false|null $sender = false;
    private MessageStat|false|null $stat = false;

    /**
     * @return MessageBody|null Body of created message. Text + attachments.
     *     Could be null if message contains only forwarded message.
     * @api
     */
    public function getBody(): ?MessageBody
    {
        return $this->body === false
            ? ($this->body = MessageBody::newFromNullableData($this->data['body'] ?? null))
            : $this->body;
    }

    /**
     * @return LinkedMessage|null Forwarded or replied message.
     * @api
     */
    public function getLink(): ?LinkedMessage
    {
        return $this->link === false
            ? ($this->link = LinkedMessage::newFromNullableData($this->data['link'] ?? null))
            : $this->link;
    }

    /**
     * @return Recipient Message recipient. Could be user or chat.
     * @api
     */
    public function getRecipient(): Recipient
    {
        return $this->recipient === false
            ? ($this->recipient = Recipient::newFromData($this->data['recipient']))
            : $this->recipient;
    }

    /**
     * @return User|null User who sent this message. Can be `null` if message has been posted on behalf of a channel.
     * @api
     */
    public function getSender(): ?User
    {
        return $this->sender === false
            ? ($this->sender = User::newFromNullableData($this->data['sender'] ?? null))
            : $this->sender;
    }

    /**
     * @return MessageStat|null Message statistics. Available only for channels in GET:/messages context.
     * @api
     */
    public function getStat(): ?MessageStat
    {
        return $this->stat === false
            ? ($this->stat = MessageStat::newFromNullableData($this->data['stat'] ?? null))
            : $this->stat;
    }

    /**
     * @return DateTimeImmutable Time when message was created.
     * @api
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['timestamp']);
    }

    /**
     * @return int Unix-time when message was created (Unix timestamp in milliseconds).
     * @api
     */
    public function getTimestampRaw(): int
    {
        return $this->data['timestamp'];
    }

    /**
     * @return string|null Message public URL. Can be `null` for dialogs or non-public chats/channels.
     * @api
     */
    public function getUrl(): ?string
    {
        return $this->data['url'] ?? null;
    }
}
