<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Request\Subscriptions;

use MaxMessenger\Api\Contracts\RequestInterface;

final readonly class DeleteSubscriptions implements RequestInterface
{
    public function __construct(
        private string $url,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRequestQuery(): array
    {
        return [
            'url' => $this->url,
        ];
    }
}
