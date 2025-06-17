<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Image attachment.
 *
 * @api
 */
readonly class PhotoAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return PhotoAttachmentPayload Image payload.
     * @api
     */
    public function getPayload(): PhotoAttachmentPayload
    {
        return PhotoAttachmentPayload::newFromData($this->data['payload']);
    }
}
