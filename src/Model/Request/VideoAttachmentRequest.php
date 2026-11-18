<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\AttachmentType;

use function array_key_exists;

/**
 * Запрос на прикрепление видео к сообщению.
 *
 * Общее количество вложений не должно превышать 12.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/media
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 */
final class VideoAttachmentRequest extends AttachmentRequest
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
     * @param UploadedInfo|null $payload Информация о загруженном видео.
     */
    public function __construct(?UploadedInfo $payload = null)
    {
        $this->required = ['payload'];

        parent::__construct(AttachmentType::Video);

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
     * @param UploadedInfo $payload Информация о загруженном видео.
     */
    public static function make(UploadedInfo $payload): self
    {
        return new self($payload);
    }

    /**
     * @param UploadedInfo|null $payload Информация о загруженном видео.
     */
    public static function new(?UploadedInfo $payload = null): self
    {
        return new self($payload);
    }

    /**
     * @param UploadedInfo $payload Информация о загруженном видео.
     * @return $this
     */
    public function setPayload(UploadedInfo $payload): self
    {
        $this->data['payload'] = $payload;

        return $this;
    }
}
