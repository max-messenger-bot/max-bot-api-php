<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Image attachment.
 *
 * @api
 */
class PhotoAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private PhotoAttachmentPayload|false $payload = false;

    /**
     * @return PhotoAttachmentPayload Image payload.
     * @api
     */
    public function getPayload(): PhotoAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = PhotoAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }
}
