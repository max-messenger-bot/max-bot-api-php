<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

use function array_key_exists;

/**
 * Запрос на прикрепление аудио к сообщению.
 *
 * ДОЛЖЕН быть единственным вложением в сообщении.
 */
final class AudioAttachmentRequest extends AttachmentRequest
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     payload: UploadedInfo
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param UploadedInfo|null $payload Информация о загруженном аудио.
     */
    public function __construct(?UploadedInfo $payload = null)
    {
        $this->required = ['payload'];

        parent::__construct(AttachmentRequestType::Audio);

        if ($payload !== null) {
            $this->setPayload($payload);
        }
    }

    public function getPayload(): UploadedInfo
    {
        return $this->data['payload'];
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    /**
     * @param UploadedInfo $payload Информация о загруженном аудио.
     */
    public static function make(UploadedInfo $payload): self
    {
        return new self($payload);
    }

    /**
     * @param UploadedInfo|null $payload Информация о загруженном аудио.
     */
    public static function new(?UploadedInfo $payload = null): self
    {
        return new self($payload);
    }

    /**
     * @param UploadedInfo $payload Информация о загруженном аудио.
     * @return $this
     */
    public function setPayload(UploadedInfo $payload): self
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
