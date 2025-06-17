<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\MessageLinkType;

/**
 * Ссылка на сообщение.
 *
 * @api
 */
final class NewMessageLink extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     type: MessageLinkType,
     *     mid: non-empty-string,
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string $mid ID сообщения исходного сообщения.
     * @param MessageLinkType $type Тип ссылки сообщения.
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
     * @param non-empty-string|null $mid ID сообщения исходного сообщения.
     * @psalm-param non-empty-string $mid
     * @param MessageLinkType|null $type Тип ссылки сообщения.
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
     * @param non-empty-string $mid ID сообщения исходного сообщения.
     * @return $this
     * @api
     */
    public function setMid(string $mid): static
    {
        static::validateString('mid', $mid, minLength: 1);

        $this->data['mid'] = $mid;

        return $this;
    }

    /**
     * @param MessageLinkType $type Тип ссылки сообщения.
     * @return $this
     * @api
     */
    public function setType(MessageLinkType $type): static
    {
        $this->data['type'] = $type;

        return $this;
    }
}
