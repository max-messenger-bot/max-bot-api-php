<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\HttpClient\HttpResponse;

use MaxMessenger\Bot\Exceptions\MaxApiException;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use Throwable;

/**
 * Base class for HTTP response exceptions.
 *
 * Abstract exception for handling HTTP response errors with response object context.
 */
abstract class HttpResponseException extends MaxApiException
{
    public function __construct(
        string $message,
        private readonly HttpResponseInterface $response,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $response->getHttpCode(), $previous);
    }

    public function getResponse(): HttpResponseInterface
    {
        return $this->response;
    }
}
