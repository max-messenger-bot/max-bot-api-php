<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вложение с файлом в сообщении.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
class FileAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     filename: string,
     *     size: non-negative-int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private FileAttachmentPayload|false $payload = false;

    /**
     * @return string Имя загруженного файла.
     */
    public function getFilename(): string
    {
        return $this->data['filename'];
    }

    /**
     * @return FileAttachmentPayload Данные прикреплённого файла.
     */
    public function getPayload(): FileAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = FileAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }

    /**
     * @return non-negative-int Размер файла в байтах.
     */
    public function getSize(): int
    {
        return $this->data['size'];
    }
}
