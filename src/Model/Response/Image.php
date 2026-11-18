<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Общий класс, описывающий объект изображения.
 *
 * @link https://dev.max.ru/docs-api/objects/Chat/
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
     * @return non-empty-string URL изображения.
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
