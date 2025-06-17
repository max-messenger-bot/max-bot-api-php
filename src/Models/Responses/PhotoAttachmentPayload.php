<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class PhotoAttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     photo_id: int,
     *     url: non-empty-string,
     *     token: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Уникальный ID этого изображения.
     */
    public function getPhotoId(): int
    {
        return $this->data['photo_id'];
    }

    /**
     * @return non-empty-string Токен изображения (minLength: 1).
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    /**
     * @return non-empty-string URL изображения (minLength: 1).
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
