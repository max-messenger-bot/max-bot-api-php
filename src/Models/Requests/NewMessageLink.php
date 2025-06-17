<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\MessageLinkType;
use MaxMessenger\Bot\Models\Responses\Message;

use function array_key_exists;

/**
 * Ссылка на сообщение.
 */
final class NewMessageLink extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     type: MessageLinkType,
     *     mid: non-empty-string,
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $mid ID сообщения исходного сообщения (minLength: 1).
     * @param MessageLinkType|null $type Тип ссылки сообщения.
     */
    public function __construct(?string $mid = null, ?MessageLinkType $type = null)
    {
        $this->required = ['type', 'mid'];

        if ($mid !== null) {
            $this->setMid($mid);
        }
        if ($type !== null) {
            $this->setType($type);
        }
    }

    /**
     * @return non-empty-string
     */
    public function getMid(): string
    {
        return $this->data['mid'];
    }

    public function getType(): MessageLinkType
    {
        return $this->data['type'];
    }

    public function issetMid(): bool
    {
        return array_key_exists('mid', $this->data);
    }

    public function issetType(): bool
    {
        return array_key_exists('type', $this->data);
    }

    /**
     * @param non-empty-string $mid ID сообщения исходного сообщения (minLength: 1).
     * @param MessageLinkType $type Тип ссылки сообщения.
     */
    public static function make(string $mid, MessageLinkType $type): self
    {
        return new self($mid, $type);
    }

    /**
     * @param non-empty-string|null $mid ID сообщения исходного сообщения (minLength: 1).
     * @param MessageLinkType|null $type Тип ссылки сообщения.
     */
    public static function new(?string $mid = null, ?MessageLinkType $type = null): self
    {
        return new self($mid, $type);
    }

    public static function newFromMessage(Message $message, MessageLinkType $type = MessageLinkType::Reply): self
    {
        return new self($message->getBody()->getMid(), $type);
    }

    /**
     * @param non-empty-string $mid ID сообщения исходного сообщения (minLength: 1).
     * @return $this
     */
    public function setMid(string $mid): self
    {
        self::validateString('mid', $mid, minLength: 1);

        $this->data['mid'] = $mid;

        return $this;
    }

    /**
     * @param MessageLinkType $type Тип ссылки сообщения.
     * @return $this
     */
    public function setType(MessageLinkType $type): self
    {
        $this->data['type'] = $type;

        return $this;
    }
}
