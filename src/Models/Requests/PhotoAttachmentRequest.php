<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Request to attach image to message.
 *
 * @api
 */
final class PhotoAttachmentRequest extends AttachmentRequest
{
    use ValidateTrait;

    /**
     * @var array{
     *     payload: PhotoAttachmentRequestPayload
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param PhotoAttachmentRequestPayload $payload Request to attach image.
     * @api
     */
    public function __construct(PhotoAttachmentRequestPayload $payload)
    {
        parent::__construct(AttachmentRequestType::Image);
        $this->setPayload($payload);
    }

    /**
     * @api
     */
    public function getPayload(): PhotoAttachmentRequestPayload
    {
        return $this->data['payload'];
    }

    /**
     * @param PhotoAttachmentRequestPayload|null $payload Request to attach image.
     * @psalm-param PhotoAttachmentRequestPayload $payload
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?PhotoAttachmentRequestPayload $payload = null): static
    {
        static::validateNotNull('payload', $payload);

        return new static($payload);
    }

    /**
     * @param PhotoAttachmentRequestPayload $payload Request to attach image.
     * @return $this
     * @api
     */
    public function setPayload(PhotoAttachmentRequestPayload $payload): static
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
