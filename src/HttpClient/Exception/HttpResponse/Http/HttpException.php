<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\HttpResponseException;
use MaxMessenger\Bot\Model\Response\Error;

/**
 * Базовый класс ошибок, о которых сообщил сервер кодом ответа.
 */
abstract class HttpException extends HttpResponseException
{
    /**
     * @param HttpRequestInterface $request Запрос, на который получен ответ.
     * @param HttpResponseInterface $response Ответ сервера.
     * @param Error|null $error Разобранное описание ошибки. `null`, если тело ответа разобрать не удалось —
     *     тогда описание ошибки можно посмотреть в теле ответа {@see getResponse()}.
     */
    public function __construct(
        HttpRequestInterface $request,
        HttpResponseInterface $response,
        public readonly ?Error $error = null,
    ) {
        parent::__construct(
            $this->error?->getMessage() ?? 'HTTP error ' . $response->getHttpCode(),
            $request,
            $response,
        );
    }

    /**
     * @return Error|null Разобранное описание ошибки или `null`, если тело ответа разобрать не удалось.
     */
    public function getError(): ?Error
    {
        return $this->error;
    }

    /**
     * Выбрасывает исключение, соответствующее коду ответа.
     *
     * @param HttpRequestInterface $request Запрос, на который получен ответ.
     * @param HttpResponseInterface $response Ответ сервера.
     * @param Error|null $error Разобранное описание ошибки.
     * @throws HttpException Всегда.
     */
    final public static function throw(
        HttpRequestInterface $request,
        HttpResponseInterface $response,
        ?Error $error = null,
    ): never {
        throw match ($response->getHttpCode()) {
            200 => new OkException($request, $response, $error),
            201 => new PartialUploadException($request, $response, $error),
            400 => new BadRequestException($request, $response, $error),
            401 => new UnauthorizedException($request, $response, $error),
            403 => new ForbiddenException($request, $response, $error),
            404 => new NotFoundException($request, $response, $error),
            405 => new NotAllowedException($request, $response, $error),
            406 => new NotAcceptableException($request, $response, $error),
            407 => new ProxyAuthenticationRequiredException($request, $response, $error),
            415 => new UnsupportedMediaTypeException($request, $response, $error),
            416 => new RangeNotSatisfiableException($request, $response, $error),
            422 => new UnprocessableEntityException($request, $response, $error),
            429 => new TooManyRequestsException($request, $response, $error),
            500 => new InternalHttpException($request, $response, $error),
            501 => new NotImplementedException($request, $response, $error),
            502 => new BadGatewayException($request, $response, $error),
            503 => new ServiceUnavailableException($request, $response, $error),
            504 => new GatewayTimeoutException($request, $response, $error),
            default => new UnknownException($request, $response, $error),
        };
    }
}
