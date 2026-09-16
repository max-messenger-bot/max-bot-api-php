<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse;

use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;
use MaxMessenger\Bot\Exception\MaxApiException;
use Throwable;

/**
 * Базовый класс ошибок ответа сервера.
 *
 * Ответ получен, но обработать его не удалось: сервер вернул ошибку либо тело ответа не соответствует ожиданиям.
 */
abstract class HttpResponseException extends MaxApiException
{
    /**
     * @param string $message Описание ошибки.
     * @param HttpRequestInterface $request Запрос, на который получен ответ.
     * @param HttpResponseInterface $response Ответ сервера.
     * @param Throwable|null $previous Исходная ошибка.
     */
    public function __construct(
        string $message,
        private readonly HttpRequestInterface $request,
        private readonly HttpResponseInterface $response,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $response->getHttpCode(), $previous);
    }

    /**
     * @return HttpRequestInterface Запрос, на который получен ответ.
     */
    public function getRequest(): HttpRequestInterface
    {
        return $this->request;
    }

    /**
     * @return HttpResponseInterface Ответ сервера.
     */
    public function getResponse(): HttpResponseInterface
    {
        return $this->response;
    }
}
