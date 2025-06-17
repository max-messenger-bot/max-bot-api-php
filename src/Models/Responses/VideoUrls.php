<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Video URLs in different resolutions.
 *
 * @api
 */
class VideoUrls extends BaseResponseModel
{
    /**
     * @var array{
     *     mp4_1080?: string|null,
     *     mp4_720?: string|null,
     *     mp4_480?: string|null,
     *     mp4_360?: string|null,
     *     mp4_240?: string|null,
     *     mp4_144?: string|null,
     *     hls?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string|null Live streaming URL, if available.
     * @api
     */
    public function getHls(): ?string
    {
        return $this->data['hls'] ?? null;
    }

    /**
     * @return string|null Video URL in 1080p resolution, if available.
     * @api
     */
    public function getMp4R1080(): ?string
    {
        return $this->data['mp4_1080'] ?? null;
    }

    /**
     * @return string|null Video URL in 144 resolution, if available.
     * @api
     */
    public function getMp4R144(): ?string
    {
        return $this->data['mp4_144'] ?? null;
    }

    /**
     * @return string|null Video URL in 240 resolution, if available.
     * @api
     */
    public function getMp4R240(): ?string
    {
        return $this->data['mp4_240'] ?? null;
    }

    /**
     * @return string|null Video URL in 360 resolution, if available.
     * @api
     */
    public function getMp4R360(): ?string
    {
        return $this->data['mp4_360'] ?? null;
    }

    /**
     * @return string|null Video URL in 480 resolution, if available.
     * @api
     */
    public function getMp4R480(): ?string
    {
        return $this->data['mp4_480'] ?? null;
    }

    /**
     * @return string|null Video URL in 720 resolution, if available.
     * @api
     */
    public function getMp4R720(): ?string
    {
        return $this->data['mp4_720'] ?? null;
    }
}
