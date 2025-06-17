<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Request\Subscriptions;

use MaxMessenger\Api\Contracts\RequestInterface;
use MaxMessenger\Api\Old\Models\Enums\UpdateType;

use function is_string;

final readonly class GetUpdates implements RequestInterface
{
    public function __construct(
        private ?int $limit,
        private ?int $timeout,
        private ?int $marker,
        private ?array $types
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRequestQuery(): array
    {
        $types = $this->types;
        if ($types !== null) {
            $types = array_map(
                static fn(UpdateType|string $value): string => is_string($value) ? $value : $value->value,
                $types
            );
            $types = implode(',', $types);
        }

        return array_filter([
            'limit' => $this->limit,
            'timeout' => $this->timeout,
            'marker' => $this->marker,
            'types' => $types,
        ], static fn(mixed $value): bool => $value !== null);
    }
}
