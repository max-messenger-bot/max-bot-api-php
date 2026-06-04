<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\MaxApiClient;

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
     * @return non-empty-string URL медиа-вложения (minLength: 1). Этот URL будет получен в объекте {@see Update}
     *     после отправки сообщения в чат. Прямую ссылку на видео также можно получить с помощью
     *     метода {@see MaxApiClient::getVideoAttachmentDetails()}.
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
