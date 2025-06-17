<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Modules;

use DateTime;

abstract class BaseResponseModel
{
    final public function __construct(
        protected readonly array $raw
    ) {
    }

    /**
     * Возвращает необработанные данные модели.
     */
    final public function getRawData(): array
    {
        return $this->raw;
    }

    protected function createDateTimeFromTimestamp(int $timestamp): DateTime
    {
        if (PHP_VERSION_ID >= 80400) {
            /**
             * @psalm-suppress UndefinedMethod
             * @var DateTime
             */
            return DateTime::createFromTimeStamp($timestamp / 1000);
        }

        return DateTime::createFromFormat('U', (string)($timestamp / 1000));
    }
}
