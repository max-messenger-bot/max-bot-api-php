<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * File attachment payload.
 *
 * @api
 */
class FileAttachmentPayload extends AttachmentPayload
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
