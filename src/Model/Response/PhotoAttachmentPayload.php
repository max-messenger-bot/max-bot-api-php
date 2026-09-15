<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\MaxApiClient;

/**
 * Данные, использованные для отправки изображения.
 */
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
     * @return non-empty-string Токен вложения — уникальный ID загруженного медиа: изображения, аудио,
     *     видео или файла. Возвращается в ответ на вызов {@see MaxApiClient::getUploadUrl()}.
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    /**
     * @return non-empty-string URL изображения. Время жизни ссылки ограниченно.
     *     Срок истечения указан в параметре `expires` — если он истёк, ссылку необходимо запросить повторно.
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
