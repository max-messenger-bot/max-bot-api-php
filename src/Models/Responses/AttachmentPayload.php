<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Generic attachment payload.
 *
 * @api
 */
abstract class AttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     url: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string Media attachment URL.
     *     For video attachments use getVideoAttachmentDetails method to obtain direct links.
     * @api
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
