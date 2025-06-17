<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

use function array_key_exists;

/**
 * Запрос на прикрепление стикера.
 *
 * ДОЛЖЕН быть единственным вложением в сообщении.
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

        parent::__construct(AttachmentRequestType::Sticker);

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
