<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\HttpClient\HttpRequest;

use JsonException;
use MaxMessenger\Bot\Exception\MaxApiException;

/**
 * JSON encoding error.
 *
 * Exception thrown when JSON encoding fails.
 */
final class JsonEncodeException extends MaxApiException
{
    public function __construct(
        public readonly array|object $data,
        JsonException $previous,
    ) {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }

    public function getData(): array|object
    {
        return $this->data;
    }
}
