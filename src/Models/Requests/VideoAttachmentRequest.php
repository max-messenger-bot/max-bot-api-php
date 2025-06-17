<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Request to attach video to message.
 *
 * @api
 */
final class VideoAttachmentRequest extends AttachmentRequest
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
     * @param UploadedInfo $payload Uploaded video info.
     * @api
     */
    public function __construct(UploadedInfo $payload)
    {
        parent::__construct(AttachmentRequestType::Video);
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
     * @param UploadedInfo|null $payload Uploaded video info.
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
     * @param UploadedInfo $payload Uploaded video info.
     * @return $this
     * @api
     */
    public function setPayload(UploadedInfo $payload): static
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
