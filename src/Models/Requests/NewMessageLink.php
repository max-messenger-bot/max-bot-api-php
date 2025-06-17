<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\MessageLinkType;

/**
 * Link to message.
 *
 * @api
 */
final class NewMessageLink extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     mid: non-empty-string,
     *     type: MessageLinkType
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param non-empty-string $mid Message identifier of original message.
     * @param MessageLinkType $type Type of message link.
     * @api
     */
    public function __construct(string $mid, MessageLinkType $type)
    {
        $this->setMid($mid);
        $this->setType($type);
    }

    /**
     * @return non-empty-string
     * @api
     */
    public function getMid(): string
    {
        return $this->data['mid'];
    }

    /**
     * @api
     */
    public function getType(): MessageLinkType
    {
        return $this->data['type'];
    }

    /**
     * @param non-empty-string|null $mid Message identifier of original message.
     * @psalm-param non-empty-string $mid
     * @param MessageLinkType|null $type Type of message link.
     * @psalm-param MessageLinkType $type
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?string $mid = null, ?MessageLinkType $type = null): static
    {
        static::validateNotNull('mid', $mid);
        static::validateNotNull('type', $type);

        return new static($mid, $type);
    }

    /**
     * @param non-empty-string $mid Message identifier of original message.
     * @api
     */
    public function setMid(string $mid): static
    {
        static::validateString('mid', $mid, minLength: 1);

        $this->data['mid'] = $mid;

        return $this;
    }

    /**
     * @param MessageLinkType $type Type of message link.
     * @api
     */
    public function setType(MessageLinkType $type): static
    {
        $this->data['type'] = $type;

        return $this;
    }
}
