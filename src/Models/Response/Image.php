<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * @api
 */
readonly class Image extends BaseResponseModel
{
    /**
     * @var array{
     *     url: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
