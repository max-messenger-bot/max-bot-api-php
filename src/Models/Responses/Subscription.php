<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;
use MaxMessenger\Bot\Models\Enums\UpdateType;

/**
 * Схема для описания подписки на WebHook.
 */
class Subscription extends BaseResponseModel
{
    /**
     * @var array{
     *     url: non-empty-string,
     *     time: int,
     *     update_types?: list<non-empty-string>,
     *     version?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return DateTimeImmutable Unix-время, когда была создана подписка.
     */
    public function getTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['time']);
    }

    /**
     * @return int Unix-время, когда была создана подписка (Unix-time).
     */
    public function getTimeRaw(): int
    {
        return $this->data['time'];
    }

    /**
     * @return list<UpdateType>|null Типы обновлений, на которые подписан бот.
     */
    public function getUpdateTypes(): ?array
    {
        return UpdateType::tryFromNullableList($this->data['update_types'] ?? null);
    }

    /**
     * @return list<non-empty-string>|null Типы обновлений, на которые подписан бот.
     */
    public function getUpdateTypesRaw(): ?array
    {
        return $this->data['update_types'] ?? null;
    }

    /**
     * @return string URL вебхука (minLength: 12).
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }

    /**
     * @return non-empty-string|null Версия API (minLength: 5, pattern: '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}').
     */
    public function getVersion(): ?string
    {
        return $this->data['version'] ?? null;
    }
}
