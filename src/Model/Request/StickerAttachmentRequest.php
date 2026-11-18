<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\AttachmentType;

use function array_key_exists;

/**
 * Запрос на прикрепление стикера.
 *
 * В одном запросе нельзя отправить больше одного стикера.
 * Стикер нельзя сочетать с другими вложениями, кроме клавиатуры.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 */
final class StickerAttachmentRequest extends AttachmentRequest
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     payload: StickerAttachmentRequestPayload
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param StickerAttachmentRequestPayload|null $payload Payload с данными стикера.
     */
    public function __construct(?StickerAttachmentRequestPayload $payload = null)
    {
        $this->required = ['payload'];

        parent::__construct(AttachmentType::Sticker);

        if ($payload !== null) {
            $this->setPayload($payload);
        }
    }

    public function getPayload(): StickerAttachmentRequestPayload
    {
        return $this->data['payload'];
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    /**
     * @param StickerAttachmentRequestPayload $payload Payload с данными стикера.
     */
    public static function make(StickerAttachmentRequestPayload $payload): self
    {
        return new self($payload);
    }

    /**
     * @param StickerAttachmentRequestPayload|null $payload Payload с данными стикера.
     */
    public static function new(?StickerAttachmentRequestPayload $payload = null): self
    {
        return new self($payload);
    }

    /**
     * @param StickerAttachmentRequestPayload $payload Payload с данными стикера.
     * @return $this
     */
    public function setPayload(StickerAttachmentRequestPayload $payload): self
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
