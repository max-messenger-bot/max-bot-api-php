<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Request\Subscriptions;

use MaxMessenger\Api\Contracts\PostRequestInterface;
use MaxMessenger\Api\Old\Models\Enums\UpdateType;

final readonly class PostSubscriptions implements PostRequestInterface
{
    /**
     * @param array<UpdateType|string>|null $updateTypes
     */
    public function __construct(
        private string $url,
        private ?array $updateTypes
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRequestQuery(): null
    {
        return null;
    }

    public function jsonSerialize(): array
    {
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        return [
            'url' => $this->url,
            ...($this->updateTypes ? ['update_types' => $this->updateTypes] : []),
        ];
    }
}
