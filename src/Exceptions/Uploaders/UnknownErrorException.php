<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Uploaders;

/**
 * Unknown error.
 *
 * Exception thrown when an unknown error occurs in the API.
 */
final class UnknownErrorException extends UploaderException
{
    public function __construct(
        public readonly string $data
    ) {
        parent::__construct('Unknown error.');
    }

    /**
     * @return string The error data that caused the exception.
     */
    public function getData(): string
    {
        return $this->data;
    }
}
