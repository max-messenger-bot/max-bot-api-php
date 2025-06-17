<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Media attachment payload.
 *
 * @api
 */
class MediaAttachmentPayload extends AttachmentPayload
{
    /**
     * @var array{
     *     token: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string Use `token` in case when you are trying to reuse the same attachment in other message.
     * @api
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }
}
