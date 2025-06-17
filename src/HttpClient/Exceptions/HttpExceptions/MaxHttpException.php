<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions\HttpExceptions;

use MaxMessenger\Bot\Models\Response\Error;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\HttpException;

/**
 * @api
 */
abstract class MaxHttpException extends HttpException
{
    public function __construct(
        HttpResponseInterface $response,
        public readonly Error $error
    ) {
        parent::__construct($response, $this->error->getMessage());
    }

    /**
     * @api
     */
    public function getError(): Error
    {
        return $this->error;
    }

    /**
     * @throws MaxHttpException
     */
    final public static function throwMax(HttpResponseInterface $response, Error $error): never
    {
        throw match ($response->getHttpCode()) {
            401 => new UnauthorizedException($response, $error),
            403 => new ForbiddenException($response, $error),
            404 => new NotFoundException($response, $error),
            405 => new NotAllowedException($response, $error),
            500 => new InternalHttpException($response, $error),
            default => new UnknownException($response, $error),
        };
    }
}
