<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Payload of ShareAttachmentRequest and ShareAttachment.
 *
 * @api
 */
readonly class ShareAttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     token?: string|null,
     *     url?: non-empty-string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string|null Attachment token.
     * @api
     */
    public function getToken(): ?string
    {
        return $this->data['token'] ?? null;
    }

    /**
     * @return non-empty-string|null URL attached to message as media preview.
     * @api
     */
    public function getUrl(): ?string
    {
        return $this->data['url'] ?? null;
    }
}
