<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;
use MaxMessenger\Bot\Models\Enums\UpdateType;

/**
 * Schema to describe WebHook subscription.
 *
 * @api
 */
class Subscription extends BaseResponseModel
{
    /**
     * @var array{
     *     time: int,
     *     update_types: list<non-empty-string>|null,
     *     url: non-empty-string,
     *     version?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return DateTimeImmutable Time when subscription was created.
     * @api
     */
    public function getTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['time']);
    }

    /**
     * @return int Unix-time when subscription was created (Unix timestamp in milliseconds).
     * @api
     */
    public function getTimeRaw(): int
    {
        return $this->data['time'];
    }

    /**
     * @return list<UpdateType>|null Update types bot subscribed for.
     * @api
     */
    public function getUpdateTypes(): ?array
    {
        return UpdateType::tryFromNullableList($this->data['update_types'] ?? null);
    }

    /**
     * @return list<non-empty-string>|null Update types bot subscribed for.
     * @api
     */
    public function getUpdateTypesRaw(): ?array
    {
        return $this->data['update_types'];
    }

    /**
     * @return non-empty-string Webhook URL.
     * @api
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }

    /**
     * @return string|null Version of API (pattern: '[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}').
     * @api
     */
    public function getVersion(): ?string
    {
        return $this->data['version'] ?? null;
    }
}
