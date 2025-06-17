<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Uploaders;

/**
 * Unknown data format.
 *
 * Exception thrown when received data is in an unknown or invalid format.
 */
final class UnknownDataFormatException extends UploaderException
{
    public function __construct(
        public readonly array $data
    ) {
        parent::__construct('Unknown data format.');
    }

    /**
     * @return array The invalid data that caused the exception.
     */
    public function getData(): array
    {
        return $this->data;
    }
}
