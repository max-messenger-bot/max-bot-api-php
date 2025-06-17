<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Message statistics.
 *
 * @api
 */
readonly class MessageStat extends BaseResponseModel
{
    /**
     * @var array{
     *     views: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return int Number of views.
     * @api
     */
    public function getViews(): int
    {
        return $this->data['views'];
    }
}
