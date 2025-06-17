<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * List of all WebHook subscriptions.
 *
 * @api
 */
class GetSubscriptionsResult extends BaseResponseModel
{
    /**
     * @var array{
     *     subscriptions: list<array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<Subscription>|false
     */
    private array|false $subscriptions = false;

    /**
     * @return list<Subscription> Current subscriptions.
     * @api
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions === false
            ? ($this->subscriptions = Subscription::newListFromData($this->data['subscriptions']))
            : $this->subscriptions;
    }
}
