<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\MaxApiClient;

/**
 * Данные прикреплённого к сообщению медиафайла.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
abstract class AttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     url: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string URL медиа-вложения. Для получения прямых ссылок на видео используйте
     *     {@see MaxApiClient::getVideoAttachmentDetails()}.
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
