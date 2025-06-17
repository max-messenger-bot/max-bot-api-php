<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class VideoAttachmentDetails extends BaseResponseModel
{
    /**
     * @var array{
     *     token: non-empty-string,
     *     urls?: array,
     *     thumbnail?: array,
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
     * @return int Длина видео в секундах.
     */
    public function getDuration(): int
    {
        return $this->data['duration'];
    }

    /**
     * @return int Высота видео.
     */
    public function getHeight(): int
    {
        return $this->data['height'];
    }

    /**
     * @return PhotoAttachmentPayload|null Миниатюра видео.
     */
    public function getThumbnail(): ?PhotoAttachmentPayload
    {
        return $this->thumbnail === false
            ? ($this->thumbnail = PhotoAttachmentPayload::newFromNullableData($this->data['thumbnail'] ?? null))
            : $this->thumbnail;
    }

    /**
     * @return non-empty-string Токен видео-вложения (minLength: 1).
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    /**
     * @return VideoUrls|null URL-ы для скачивания или воспроизведения видео. Может быть null, если видео недоступно.
     */
    public function getUrls(): ?VideoUrls
    {
        return $this->urls === false
            ? ($this->urls = VideoUrls::newFromNullableData($this->data['urls'] ?? null))
            : $this->urls;
    }

    /**
     * @return int Ширина видео.
     */
    public function getWidth(): int
    {
        return $this->data['width'];
    }
}
