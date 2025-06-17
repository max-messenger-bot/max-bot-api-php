<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions;

use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\ParseDataException;

/**
 * Unexpected response format.
 *
 * Exception thrown when the response format is not as expected.
 */
final class UnexpectedFormatException extends ParseDataException
{
    public function __construct(HttpResponseInterface $response)
    {
        parent::__construct('Unexpected Response Format.', 0, $response);
    }
}
