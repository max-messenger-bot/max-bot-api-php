<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Общая схема, описывающая объект изображения.
 */
class Image extends BaseResponseModel
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
}
