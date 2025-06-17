<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Request to attach sticker.
 *
 * MUST be the only attachment request in message.
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
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param StickerAttachmentRequestPayload $payload Sticker attachment payload.
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
     * @param StickerAttachmentRequestPayload|null $payload Sticker attachment payload.
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
     * @param StickerAttachmentRequestPayload $payload Sticker attachment payload.
     * @return $this
     * @api
     */
    public function setPayload(StickerAttachmentRequestPayload $payload): static
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
