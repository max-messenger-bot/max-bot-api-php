<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Sticker attachment payload.
 *
 * @api
 */
class StickerAttachmentPayload extends AttachmentPayload
{
    /**
     * @var array{
     *     code: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string Sticker identifier.
     * @api
     */
    public function getCode(): string
    {
        return $this->data['code'];
    }
}
