<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

use function array_key_exists;
use function is_array;

/**
 * Запрос на прикрепление клавиатуры к сообщению.
 */
final class InlineKeyboardAttachmentRequest extends AttachmentRequest
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     payload: InlineKeyboardAttachmentRequestPayload
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param InlineKeyboardAttachmentRequestPayload|non-empty-array<non-empty-array<Button>>|null $payloadOrButtons
     *     Payload с данными клавиатуры или двумерный массив кнопок.
     */
    public function __construct(InlineKeyboardAttachmentRequestPayload|array|null $payloadOrButtons = null)
    {
        $this->required = ['payload'];

        parent::__construct(AttachmentRequestType::InlineKeyboard);

        if (is_array($payloadOrButtons)) {
            $this->setPayload(new InlineKeyboardAttachmentRequestPayload($payloadOrButtons));
        } elseif ($payloadOrButtons !== null) {
            $this->setPayload($payloadOrButtons);
        }
    }

    public function getPayload(): InlineKeyboardAttachmentRequestPayload
    {
        return $this->data['payload'];
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    /**
     * @param InlineKeyboardAttachmentRequestPayload|non-empty-array<non-empty-array<Button>> $payloadOrButtons
     *     Payload с данными клавиатуры или двумерный массив кнопок.
     */
    public static function make(InlineKeyboardAttachmentRequestPayload|array $payloadOrButtons): self
    {
        return new self($payloadOrButtons);
    }

    /**
     * @param InlineKeyboardAttachmentRequestPayload|non-empty-array<non-empty-array<Button>>|null $payloadOrButtons
     *     Payload с данными клавиатуры или двумерный массив кнопок
     */
    public static function new(InlineKeyboardAttachmentRequestPayload|array|null $payloadOrButtons = null): self
    {
        return new self($payloadOrButtons);
    }

    /**
     * @param InlineKeyboardAttachmentRequestPayload $payload Payload с данными клавиатуры.
     * @return $this
     */
    public function setPayload(InlineKeyboardAttachmentRequestPayload $payload): self
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
