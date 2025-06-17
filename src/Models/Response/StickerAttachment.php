<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Sticker attachment.
 *
 * @api
 */
readonly class StickerAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     width: int,
     *     height: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

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
        return StickerAttachmentPayload::newFromData($this->data['payload']);
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
