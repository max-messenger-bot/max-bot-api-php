<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * File attachment.
 *
 * @api
 */
class FileAttachment extends Attachment
{
    /**
     * @var array{
     *     filename: string,
     *     payload: array,
     *     size: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private FileAttachmentPayload|false $payload = false;

    /**
     * @return string Uploaded file name.
     * @api
     */
    public function getFilename(): string
    {
        return $this->data['filename'];
    }

    /**
     * @return FileAttachmentPayload File payload.
     * @api
     */
    public function getPayload(): FileAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = FileAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }

    /**
     * @return int File size in bytes.
     * @api
     */
    public function getSize(): int
    {
        return $this->data['size'];
    }
}
