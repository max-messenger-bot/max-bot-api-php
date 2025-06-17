<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Video thumbnail.
 *
 * @api
 */
readonly class VideoThumbnail extends BaseResponseModel
{
    /**
     * @var array{
     *     url: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string Image URL.
     * @api
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
