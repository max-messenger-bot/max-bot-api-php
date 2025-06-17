<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Request to attach contact card to message.
 *
 * MUST be the only attachment in message.
 *
 * @api
 */
final class ContactAttachmentRequest extends AttachmentRequest
{
    use ValidateTrait;

    /**
     * @var array{
     *     payload: ContactAttachmentRequestPayload
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param ContactAttachmentRequestPayload $payload Contact attachment payload.
     * @api
     */
    public function __construct(ContactAttachmentRequestPayload $payload)
    {
        parent::__construct(AttachmentRequestType::Contact);
        $this->setPayload($payload);
    }

    /**
     * @api
     */
    public function getPayload(): ContactAttachmentRequestPayload
    {
        return $this->data['payload'];
    }

    /**
     * @param ContactAttachmentRequestPayload|null $payload Contact attachment payload.
     * @psalm-param ContactAttachmentRequestPayload $payload
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?ContactAttachmentRequestPayload $payload = null): static
    {
        static::validateNotNull('payload', $payload);

        return new static($payload);
    }

    /**
     * @param ContactAttachmentRequestPayload $payload Contact attachment payload.
     * @return $this
     * @api
     */
    public function setPayload(ContactAttachmentRequestPayload $payload): static
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
