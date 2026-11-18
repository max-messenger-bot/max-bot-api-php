<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вложение с аудиосообщением или аудиофайлом.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
class AudioAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private MediaAttachmentPayload|false $payload = false;

    /**
     * @return MediaAttachmentPayload Данные прикреплённого аудиосообщения или аудиофайла.
     */
    public function getPayload(): MediaAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = MediaAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }
}
