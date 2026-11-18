<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Закодированная информация загруженного изображения.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/media
 */
class PhotoToken extends BaseResponseModel
{
    /**
     * @var array{
     *     token: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Закодированная информация загруженного изображения.
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }
}
