<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Image attachment payload.
 *
 * @api
 */
class PhotoAttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     photo_id: int,
     *     token: string,
     *     url: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Unique identifier of this image.
     * @api
     */
    public function getPhotoId(): int
    {
        return $this->data['photo_id'];
    }

    /**
     * @api
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    /**
     * @return string Image URL.
     * @api
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
