<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use MaxMessenger\Bot\Models\Enums\MessageLinkType;

/**
 * Linked message information.
 *
 * @api
 */
readonly class LinkedMessage extends BaseResponseModel
{
    /**
     * @var array{
     *     type: string,
     *     sender?: array|null,
     *     chat_id?: int|null,
     *     message: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return int|null Chat where message has been originally posted. For forwarded messages only.
     * @api
     */
    public function getChatId(): ?int
    {
        return $this->data['chat_id'] ?? null;
    }

    /**
     * @return MessageBody Message body.
     * @api
     */
    public function getMessage(): MessageBody
    {
        return MessageBody::newFromData($this->data['message']);
    }

    /**
     * @return User|null User sent this message. Can be `null` if message has been posted on behalf of a channel.
     * @api
     */
    public function getSender(): ?User
    {
        return User::newFromNullableData($this->data['sender'] ?? null);
    }

    /**
     * @return MessageLinkType Type of linked message.
     * @api
     */
    public function getType(): MessageLinkType
    {
        return MessageLinkType::from($this->data['type']);
    }

    /**
     * @return string Type of linked message.
     * @api
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }
}
