<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Sticker attachment payload.
 *
 * @api
 */
readonly class StickerAttachmentPayload extends AttachmentPayload
{
    /**
     * @var array{
     *     code: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string Sticker identifier.
     * @api
     */
    public function getCode(): string
    {
        return $this->data['code'];
    }
}
