<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

use function array_key_exists;

/**
 * Запрос на прикрепление изображения к сообщению.
 */
final class PhotoAttachmentRequest extends AttachmentRequest
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     payload: PhotoAttachmentRequestPayload
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param PhotoAttachmentRequestPayload|null $payload Запрос на прикрепление изображения.
     * @api
     */
    public function __construct(?PhotoAttachmentRequestPayload $payload = null)
    {
        $this->required = ['payload'];

        parent::__construct(AttachmentRequestType::Image);

        if ($payload !== null) {
            $this->setPayload($payload);
        }
    }

    public function getPayload(): PhotoAttachmentRequestPayload
    {
        return $this->data['payload'];
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    /**
     * @param PhotoAttachmentRequestPayload $payload Запрос на прикрепление изображения.
     */
    public static function make(PhotoAttachmentRequestPayload $payload): self
    {
        return new self($payload);
    }

    /**
     * @param PhotoAttachmentRequestPayload|null $payload Запрос на прикрепление изображения.
     * @api
     */
    public static function new(?PhotoAttachmentRequestPayload $payload = null): self
    {
        return new self($payload);
    }

    /**
     * @param PhotoAttachmentRequestPayload $payload Запрос на прикрепление изображения.
     * @return $this
     */
    public function setPayload(PhotoAttachmentRequestPayload $payload): self
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
