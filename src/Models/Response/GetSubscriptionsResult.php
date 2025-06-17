<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * List of all WebHook subscriptions.
 *
 * @api
 */
readonly class GetSubscriptionsResult extends BaseResponseModel
{
    /**
     * @var array{
     *     subscriptions: list<array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return list<Subscription> Current subscriptions.
     * @api
     */
    public function getSubscriptions(): array
    {
        return Subscription::newListFromData($this->data['subscriptions']);
    }
}
