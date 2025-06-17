<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * File attachment payload.
 *
 * @api
 */
readonly class FileAttachmentPayload extends AttachmentPayload
{
    /**
     * @var array{
     *     token: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string Use `token` in case when you are trying to reuse the same attachment in other message.
     * @api
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }
}
