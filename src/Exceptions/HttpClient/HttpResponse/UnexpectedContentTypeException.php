<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\HttpClient\HttpResponse;

use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;

/**
 * Unexpected Content-Type.
 *
 * Exception thrown when the response Content-Type is not as expected.
 */
final class UnexpectedContentTypeException extends HttpResponseException
{
    public function __construct(HttpResponseInterface $response)
    {
        parent::__construct('Unexpected ContentType.', $response);
    }
}
