<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class VideoUrls extends BaseResponseModel
{
    /**
     * @var array{
     *     mp4_1080?: non-empty-string,
     *     mp4_720?: non-empty-string,
     *     mp4_480?: non-empty-string,
     *     mp4_360?: non-empty-string,
     *     mp4_240?: non-empty-string,
     *     mp4_144?: non-empty-string,
     *     hls?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string|null URL трансляции, если доступно (minLength: 1).
     */
    public function getHls(): ?string
    {
        return $this->data['hls'] ?? null;
    }

    /**
     * @return non-empty-string|null URL видео в разрешении 1080p, если доступно (minLength: 1).
     */
    public function getMp4R1080(): ?string
    {
        return $this->data['mp4_1080'] ?? null;
    }

    /**
     * @return non-empty-string|null URL видео в разрешении 144p, если доступно (minLength: 1).
     */
    public function getMp4R144(): ?string
    {
        return $this->data['mp4_144'] ?? null;
    }

    /**
     * @return non-empty-string|null URL видео в разрешении 240p, если доступно (minLength: 1).
     */
    public function getMp4R240(): ?string
    {
        return $this->data['mp4_240'] ?? null;
    }

    /**
     * @return non-empty-string|null URL видео в разрешении 360p, если доступно (minLength: 1).
     */
    public function getMp4R360(): ?string
    {
        return $this->data['mp4_360'] ?? null;
    }

    /**
     * @return non-empty-string|null URL видео в разрешении 480p, если доступно (minLength: 1).
     */
    public function getMp4R480(): ?string
    {
        return $this->data['mp4_480'] ?? null;
    }

    /**
     * @return non-empty-string|null URL видео в разрешении 720p, если доступно (minLength: 1).
     */
    public function getMp4R720(): ?string
    {
        return $this->data['mp4_720'] ?? null;
    }
}
