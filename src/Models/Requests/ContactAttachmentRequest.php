<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Запрос на прикрепление карточки контакта к сообщению. ДОЛЖЕН быть единственным вложением в сообщении.
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
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param ContactAttachmentRequestPayload $payload Payload с данными контакта.
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
     * @param ContactAttachmentRequestPayload|null $payload Payload с данными контакта.
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
     * @param ContactAttachmentRequestPayload $payload Payload с данными контакта.
     * @return $this
     * @api
     */
    public function setPayload(ContactAttachmentRequestPayload $payload): static
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
