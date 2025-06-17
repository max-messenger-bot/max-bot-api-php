<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Запрос на прикрепление аудио к сообщению.
 *
 * ДОЛЖЕН быть единственным вложением в сообщении.
 *
 * @api
 */
final class AudioAttachmentRequest extends AttachmentRequest
{
    use ValidateTrait;

    /**
     * @var array{
     *     payload: UploadedInfo
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param UploadedInfo $payload Информация о загруженном аудио.
     * @api
     */
    public function __construct(UploadedInfo $payload)
    {
        parent::__construct(AttachmentRequestType::Audio);
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
     * @param UploadedInfo|null $payload Информация о загруженном аудио.
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
     * @param UploadedInfo $payload Информация о загруженном аудио.
     * @return $this
     * @api
     */
    public function setPayload(UploadedInfo $payload): static
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
