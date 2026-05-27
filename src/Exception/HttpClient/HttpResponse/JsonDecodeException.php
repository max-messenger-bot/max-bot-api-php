<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\HttpClient\HttpResponse;

use JsonException;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;

/**
 * JSON decode error.
 *
 * Exception thrown when JSON decoding fails or encounters an error.
 */
final class JsonDecodeException extends ParseDataException
{
    public function __construct(HttpResponseInterface $response, ?JsonException $previous = null)
    {
        parent::__construct('JSON decode error.', $response, $previous);
    }
}
