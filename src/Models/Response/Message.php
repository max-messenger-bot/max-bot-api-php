<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use DateTimeImmutable;

/**
 * Message in chat.
 *
 * @api
 */
readonly class Message extends BaseResponseModel
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
    protected array $data;

    /**
     * @return MessageBody|null Body of created message. Text + attachments.
     *     Could be null if message contains only forwarded message.
     * @api
     */
    public function getBody(): ?MessageBody
    {
        return MessageBody::newFromNullableData($this->data['body'] ?? null);
    }

    /**
     * @return LinkedMessage|null Forwarded or replied message.
     * @api
     */
    public function getLink(): ?LinkedMessage
    {
        return LinkedMessage::newFromNullableData($this->data['link'] ?? null);
    }

    /**
     * @return Recipient Message recipient. Could be user or chat.
     * @api
     */
    public function getRecipient(): Recipient
    {
        return Recipient::newFromData($this->data['recipient']);
    }

    /**
     * @return User|null User who sent this message. Can be `null` if message has been posted on behalf of a channel.
     * @api
     */
    public function getSender(): ?User
    {
        return User::newFromNullableData($this->data['sender'] ?? null);
    }

    /**
     * @return MessageStat|null Message statistics. Available only for channels in GET:/messages context.
     * @api
     */
    public function getStat(): ?MessageStat
    {
        return MessageStat::newFromNullableData($this->data['stat'] ?? null);
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
     * @return int Time when message was created (Unix timestamp in milliseconds).
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
