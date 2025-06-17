<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Request to attach file to message.
 *
 * MUST be the only attachment in message.
 *
 * @api
 */
final class FileAttachmentRequest extends AttachmentRequest
{
    use ValidateTrait;

    /**
     * @var array{
     *     payload: UploadedInfo
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param UploadedInfo $payload Uploaded file info.
     * @api
     */
    public function __construct(UploadedInfo $payload)
    {
        parent::__construct(AttachmentRequestType::File);
        $this->setPayload($payload);
    }

    /**
     * @api
     */
    public function getPayload(): UploadedInfo
    {
        return $this->data['payload'];
    }

    /**
     * @param UploadedInfo|null $payload Uploaded file info.
     * @psalm-param UploadedInfo $payload
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?UploadedInfo $payload = null): static
    {
        static::validateNotNull('payload', $payload);

        return new static($payload);
    }

    /**
     * @param UploadedInfo $payload Uploaded file info.
     * @return $this
     * @api
     */
    public function setPayload(UploadedInfo $payload): static
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
