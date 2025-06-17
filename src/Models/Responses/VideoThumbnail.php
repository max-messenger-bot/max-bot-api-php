<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

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
     * @return non-empty-string URL изображения (minLength: 1).
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }

    public static function newFromUrl(string $url): static
    {
        /** @psalm-var static Bug? */
        return static::newFromData(['url' => $url]);
    }
}
