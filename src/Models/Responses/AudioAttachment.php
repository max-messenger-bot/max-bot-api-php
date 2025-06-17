<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Audio attachment.
 *
 * @api
 */
class AudioAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     transcription?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private MediaAttachmentPayload|false $payload = false;

    /**
     * @return MediaAttachmentPayload Audio payload.
     * @api
     */
    public function getPayload(): MediaAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = MediaAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }

    /**
     * @return string|null Audio transcription.
     * @api
     */
    public function getTranscription(): ?string
    {
        return $this->data['transcription'] ?? null;
    }
}
