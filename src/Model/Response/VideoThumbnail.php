<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Миниатюра видео.
 *
 * @link https://dev.max.ru/docs-api/methods/GET/videos/-videoToken-/
 */
class VideoThumbnail extends BaseResponseModel
{
    /**
     * @var array{
     *     url: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string URL изображения.
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }

    /**
     * Создаёт объект с URL миниатюры видео.
     *
     * @param non-empty-string $url URL изображения миниатюры.
     */
    public static function newFromUrl(string $url): static
    {
        return static::newFromData(['url' => $url]);
    }
}
