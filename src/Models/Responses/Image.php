<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * @api
 */
class Image extends BaseResponseModel
{
    /**
     * @var array{
     *     url: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
