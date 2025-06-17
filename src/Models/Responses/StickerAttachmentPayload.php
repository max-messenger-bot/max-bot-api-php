<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class StickerAttachmentPayload extends AttachmentPayload
{
    /**
     * @var array{
     *     code: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string ID стикера (minLength: 1).
     */
    public function getCode(): string
    {
        return $this->data['code'];
    }
}
