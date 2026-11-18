<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\AttachmentType;

use function array_key_exists;

/**
 * Запрос на прикрепление карточки контакта к сообщению.
 *
 * Нельзя прикреплять несколько контактов или сочетать контакт с другими вложениями, кроме клавиатуры.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 */
final class ContactAttachmentRequest extends AttachmentRequest
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     payload: ContactAttachmentRequestPayload
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param ContactAttachmentRequestPayload|null $payload Payload с данными контакта.
     */
    public function __construct(?ContactAttachmentRequestPayload $payload = null)
    {
        $this->required = ['payload'];

        parent::__construct(AttachmentType::Contact);

        if ($payload !== null) {
            $this->setPayload($payload);
        }
    }

    public function getPayload(): ContactAttachmentRequestPayload
    {
        return $this->data['payload'];
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    /**
     * @param ContactAttachmentRequestPayload $payload Payload с данными контакта.
     */
    public static function make(ContactAttachmentRequestPayload $payload): self
    {
        return new self($payload);
    }

    /**
     * @param ContactAttachmentRequestPayload|null $payload Payload с данными контакта.
     */
    public static function new(?ContactAttachmentRequestPayload $payload = null): self
    {
        return new self($payload);
    }

    /**
     * @param ContactAttachmentRequestPayload $payload Payload с данными контакта.
     * @return $this
     */
    public function setPayload(ContactAttachmentRequestPayload $payload): self
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
