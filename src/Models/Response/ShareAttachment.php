<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Share attachment.
 *
 * @api
 */
readonly class ShareAttachment extends Attachment
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
    protected array $data;

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
        return ShareAttachmentPayload::newFromData($this->data['payload']);
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
