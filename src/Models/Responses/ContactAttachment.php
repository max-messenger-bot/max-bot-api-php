<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Contact attachment.
 *
 * @api
 */
class ContactAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private ContactAttachmentPayload|false $payload = false;

    /**
     * @return ContactAttachmentPayload Contact payload.
     * @api
     */
    public function getPayload(): ContactAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = ContactAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }
}
