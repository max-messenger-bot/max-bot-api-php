<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\HttpClient\HttpResponse;

use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;

/**
 * Unexpected response format.
 *
 * Exception thrown when the response format is not as expected.
 */
final class UnexpectedFormatException extends ParseDataException
{
    public function __construct(HttpResponseInterface $response)
    {
        parent::__construct('Unexpected Response Format.', $response);
    }
}
