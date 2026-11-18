<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вложение изображения.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
class PhotoAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private PhotoAttachmentPayload|false $payload = false;

    /**
     * @return PhotoAttachmentPayload Данные прикреплённого изображения.
     */
    public function getPayload(): PhotoAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = PhotoAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }
}
