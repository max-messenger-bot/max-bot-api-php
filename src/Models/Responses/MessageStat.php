<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Message statistics.
 *
 * @api
 */
class MessageStat extends BaseResponseModel
{
    /**
     * @var array{
     *     views: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Number of views.
     * @api
     */
    public function getViews(): int
    {
        return $this->data['views'];
    }
}
