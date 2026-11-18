<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use function is_string;

/**
 * Вложение с видео в сообщении.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
class VideoAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     thumbnail?: array|non-empty-string,
     *     width?: non-negative-int,
     *     height?: non-negative-int,
     *     duration?: non-negative-int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private MediaAttachmentPayload|false $payload = false;
    private VideoThumbnail|false|null $thumbnail = false;

    /**
     * @return non-negative-int|null Длина видео в секундах.
     */
    public function getDuration(): ?int
    {
        return $this->data['duration'] ?? null;
    }

    /**
     * @return non-negative-int|null Высота видео.
     */
    public function getHeight(): ?int
    {
        return $this->data['height'] ?? null;
    }

    /**
     * @return MediaAttachmentPayload Данные прикреплённого видео.
     */
    public function getPayload(): MediaAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = MediaAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }

    /**
     * @return VideoThumbnail|null Миниатюра видео.
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
     * @return non-negative-int|null Ширина видео.
     */
    public function getWidth(): ?int
    {
        return $this->data['width'] ?? null;
    }
}
