<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Contact attachment.
 *
 * @api
 */
readonly class ContactAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return ContactAttachmentPayload Contact payload.
     * @api
     */
    public function getPayload(): ContactAttachmentPayload
    {
        return ContactAttachmentPayload::newFromData($this->data['payload']);
    }
}
