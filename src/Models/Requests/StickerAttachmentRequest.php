<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Запрос на прикрепление стикера. ДОЛЖЕН быть единственным вложением в сообщении.
 *
 * @api
 */
final class StickerAttachmentRequest extends AttachmentRequest
{
    use ValidateTrait;

    /**
     * @var array{
     *     payload: StickerAttachmentRequestPayload
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param StickerAttachmentRequestPayload $payload Payload с данными стикера.
     * @api
     */
    public function __construct(StickerAttachmentRequestPayload $payload)
    {
        parent::__construct(AttachmentRequestType::Sticker);
        $this->setPayload($payload);
    }

    /**
     * @api
     */
    public function getPayload(): StickerAttachmentRequestPayload
    {
        return $this->data['payload'];
    }

    /**
     * @param StickerAttachmentRequestPayload|null $payload Payload с данными стикера.
     * @psalm-param StickerAttachmentRequestPayload $payload
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?StickerAttachmentRequestPayload $payload = null): static
    {
        static::validateNotNull('payload', $payload);

        return new static($payload);
    }

    /**
     * @param StickerAttachmentRequestPayload $payload Payload с данными стикера.
     * @return $this
     * @api
     */
    public function setPayload(StickerAttachmentRequestPayload $payload): static
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
