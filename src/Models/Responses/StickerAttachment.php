<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Sticker attachment.
 *
 * @api
 */
class StickerAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     width: int,
     *     height: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private StickerAttachmentPayload|false $payload = false;

    /**
     * @return int Sticker height.
     * @api
     */
    public function getHeight(): int
    {
        return $this->data['height'];
    }

    /**
     * @return StickerAttachmentPayload Sticker payload.
     * @api
     */
    public function getPayload(): StickerAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = StickerAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }

    /**
     * @return int Sticker width.
     * @api
     */
    public function getWidth(): int
    {
        return $this->data['width'];
    }
}
