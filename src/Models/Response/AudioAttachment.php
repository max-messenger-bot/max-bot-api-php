<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Audio attachment.
 *
 * @api
 */
readonly class AudioAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     transcription?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return MediaAttachmentPayload Audio payload.
     * @api
     */
    public function getPayload(): MediaAttachmentPayload
    {
        return MediaAttachmentPayload::newFromData($this->data['payload']);
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
