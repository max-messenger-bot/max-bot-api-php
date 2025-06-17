<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use function is_string;

/**
 * Video attachment.
 *
 * @api
 */
class VideoAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     thumbnail?: array|string|null,
     *     width?: int|null,
     *     height?: int|null,
     *     duration?: int|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private MediaAttachmentPayload|false $payload = false;
    private VideoThumbnail|false|null $thumbnail = false;

    /**
     * @return int|null Video duration in seconds.
     * @api
     */
    public function getDuration(): ?int
    {
        return $this->data['duration'] ?? null;
    }

    /**
     * @return int|null Video height.
     * @api
     */
    public function getHeight(): ?int
    {
        return $this->data['height'] ?? null;
    }

    /**
     * @return MediaAttachmentPayload Video payload.
     * @api
     */
    public function getPayload(): MediaAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = MediaAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }

    /**
     * @return VideoThumbnail|null Video thumbnail.
     * @api
     */
    public function getThumbnail(): ?VideoThumbnail
    {
        if ($this->thumbnail !== false) {
            return $this->thumbnail;
        }

        $thumbnail = $this->data['thumbnail'] ?? null;

        return $this->thumbnail = is_string($thumbnail)
            ? VideoThumbnail::newFromUrl($thumbnail)
            : VideoThumbnail::newFromNullableData($thumbnail);
    }

    /**
     * @return int|null Video width.
     * @api
     */
    public function getWidth(): ?int
    {
        return $this->data['width'] ?? null;
    }
}
