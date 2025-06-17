<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Share attachment.
 *
 * @api
 */
class ShareAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     title?: string|null,
     *     description?: string|null,
     *     image_url?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private ShareAttachmentPayload|false $payload = false;

    /**
     * @return string|null Link preview description.
     * @api
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return string|null Link preview image URL.
     * @api
     */
    public function getImageUrl(): ?string
    {
        return $this->data['image_url'] ?? null;
    }

    /**
     * @return ShareAttachmentPayload Share payload.
     * @api
     */
    public function getPayload(): ShareAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = ShareAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }

    /**
     * @return string|null Link preview title.
     * @api
     */
    public function getTitle(): ?string
    {
        return $this->data['title'] ?? null;
    }
}
