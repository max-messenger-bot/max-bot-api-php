<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Запрос на прикрепление файла к сообщению. ДОЛЖЕН быть единственным вложением в сообщении.
 *
 * @api
 */
final class FileAttachmentRequest extends AttachmentRequest
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
     * @param UploadedInfo $payload Информация о загруженном файле.
     * @api
     */
    public function __construct(UploadedInfo $payload)
    {
        parent::__construct(AttachmentRequestType::File);
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
     * @param UploadedInfo|null $payload Информация о загруженном файле.
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
     * @param UploadedInfo $payload Информация о загруженном файле.
     * @return $this
     * @api
     */
    public function setPayload(UploadedInfo $payload): static
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
