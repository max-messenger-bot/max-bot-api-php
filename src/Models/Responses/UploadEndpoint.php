<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Endpoint you should upload to your binaries.
 *
 * @api
 */
class UploadEndpoint extends BaseResponseModel
{
    /**
     * @var array{
     *     url: string,
     *     token?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string|null Video or audio token for send message.
     * @api
     */
    public function getToken(): ?string
    {
        return $this->data['token'] ?? null;
    }

    /**
     * @return string URL to upload.
     * @api
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
