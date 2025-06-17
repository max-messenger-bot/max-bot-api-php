<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * File attachment.
 *
 * @api
 */
readonly class FileAttachment extends Attachment
{
    /**
     * @var array{
     *     filename: string,
     *     payload: array,
     *     size: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

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
        return FileAttachmentPayload::newFromData($this->data['payload']);
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
