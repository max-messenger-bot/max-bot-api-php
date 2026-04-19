<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\HttpClient\HttpRequest;

use JsonException;
use MaxMessenger\Bot\Exceptions\MaxApiException;

/**
 * JSON encoding error.
 *
 * Exception thrown when JSON encoding fails.
 */
final class JsonEncodeException extends MaxApiException
{
    public function __construct(
        public readonly array|object $data,
        JsonException $previous
    ) {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }

    public function getData(): array|object
    {
        return $this->data;
    }
}
