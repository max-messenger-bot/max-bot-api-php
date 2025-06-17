<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Detailed video attachment info.
 *
 * @api
 */
class VideoAttachmentDetails extends BaseResponseModel
{
    /**
     * @var array{
     *     token: string,
     *     urls?: array|null,
     *     thumbnail?: array|null,
     *     width: int,
     *     height: int,
     *     duration: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private PhotoAttachmentPayload|false|null $thumbnail = false;
    private VideoUrls|false|null $urls = false;

    /**
     * @return int Video duration in seconds.
     * @api
     */
    public function getDuration(): int
    {
        return $this->data['duration'];
    }

    /**
     * @return int Video height.
     * @api
     */
    public function getHeight(): int
    {
        return $this->data['height'];
    }

    /**
     * @return PhotoAttachmentPayload|null Video thumbnail.
     * @api
     */
    public function getThumbnail(): ?PhotoAttachmentPayload
    {
        return $this->thumbnail === false
            ? ($this->thumbnail = PhotoAttachmentPayload::newFromNullableData($this->data['thumbnail'] ?? null))
            : $this->thumbnail;
    }

    /**
     * @return string Video attachment token.
     * @api
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    /**
     * @return VideoUrls|null URLs to download or play video. Can be null if video is unavailable.
     * @api
     */
    public function getUrls(): ?VideoUrls
    {
        return $this->urls === false
            ? ($this->urls = VideoUrls::newFromNullableData($this->data['urls'] ?? null))
            : $this->urls;
    }

    /**
     * @return int Video width.
     * @api
     */
    public function getWidth(): int
    {
        return $this->data['width'];
    }
}
