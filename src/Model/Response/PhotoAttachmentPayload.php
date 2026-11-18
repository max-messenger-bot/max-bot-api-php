<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Данные, использованные для отправки изображения.
 *
 * @link https://dev.max.ru/docs-api/objects/Message
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/media
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
     * @return int Уникальный идентификатор этого изображения.
     */
    public function getPhotoId(): int
    {
        return $this->data['photo_id'];
    }

    /**
     * @return non-empty-string Токен загруженного изображения.
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
