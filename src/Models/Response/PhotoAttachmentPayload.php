<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Image attachment payload.
 *
 * @api
 */
readonly class PhotoAttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     photo_id: int,
     *     token: string,
     *     url: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

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
