<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions;

use DateTime;
use MaxMessenger\Api\Modules\BaseResponseModel;
use MaxMessenger\Api\Old\Models\Enums\UpdateType;

/**
 * @api
 */
final class Subscription extends BaseResponseModel
{
    /**
     * Необработанные типы обновлений, на которые подписан бот.
     *
     * @return string[]|null
     */
    public function getRawUpdateTypes(): ?array
    {
        /** @var string[]|null */
        return $this->raw['update_types'] ?? null;
    }

    /**
     * Время, когда была создана подписка.
     */
    public function getTime(): DateTime
    {
        return $this->createDateTimeFromTimestamp($this->getTimestamp());
    }

    /**
     * Unix-время, когда была создана подписка.
     */
    public function getTimestamp(): int
    {
        /** @var int */
        return $this->raw['time'];
    }

    /**
     * Типы обновлений, на которые подписан бот.
     *
     * Типы не представленные классом {@see UpdateType} будут отфильтрованы.
     * Для получения полного списка, используйте метод {@see getRawUpdateTypes()}
     *
     * @return UpdateType[]|null
     */
    public function getUpdateTypes(): ?array
    {
        $rawTypes = $this->getRawUpdateTypes();

        if ($rawTypes === null) {
            return null;
        }

        return array_filter(
            array_map(
                static fn(string $type): ?UpdateType => UpdateType::tryFrom($type),
                $rawTypes
            )
        );
    }

    /**
     * URL вебхука.
     */
    public function getUrl(): string
    {
        /** @var string */
        return $this->raw['url'];
    }
}
