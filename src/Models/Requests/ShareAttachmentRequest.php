<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

use function array_key_exists;

/**
 * Запрос на прикрепление предпросмотра медиафайла по-внешнему URL.
 */
final class ShareAttachmentRequest extends AttachmentRequest
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     payload: ShareAttachmentPayload
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param ShareAttachmentPayload|null $payload Payload с данными вложения.
     */
    public function __construct(?ShareAttachmentPayload $payload = null)
    {
        $this->required = ['payload'];

        parent::__construct(AttachmentRequestType::Share);

        if ($payload !== null) {
            $this->setPayload($payload);
        }
    }

    public function getPayload(): ShareAttachmentPayload
    {
        return $this->data['payload'];
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    /**
     * @param ShareAttachmentPayload $payload Payload с данными вложения.
     */
    public static function make(ShareAttachmentPayload $payload): self
    {
        return new self($payload);
    }

    /**
     * @param ShareAttachmentPayload|null $payload Payload с данными вложения.
     */
    public static function new(?ShareAttachmentPayload $payload = null): self
    {
        return new self($payload);
    }

    /**
     * @param ShareAttachmentPayload $payload Payload с данными вложения.
     * @return $this
     */
    public function setPayload(ShareAttachmentPayload $payload): self
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
