<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Список всех WebHook подписок.
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
     * @return list<Subscription> Список текущих подписок.
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions === false
            ? ($this->subscriptions = Subscription::newListFromData($this->data['subscriptions']))
            : $this->subscriptions;
    }
}
