<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use function is_string;

/**
 * Video attachment.
 *
 * @api
 */
readonly class VideoAttachment extends Attachment
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
    protected array $data;

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
        return MediaAttachmentPayload::newFromData($this->data['payload']);
    }

    /**
     * @return VideoThumbnail|null Video thumbnail.
     * @api
     */
    public function getThumbnail(): ?VideoThumbnail
    {
        $thumbnail = $this->data['thumbnail'] ?? null;

        return is_string($thumbnail)
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
