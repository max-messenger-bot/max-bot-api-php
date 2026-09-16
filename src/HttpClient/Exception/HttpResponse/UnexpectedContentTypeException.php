<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse;

use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;

/**
 * Неожиданный `Content-Type` ответа.
 */
final class UnexpectedContentTypeException extends HttpResponseException
{
    /**
     * @param HttpRequestInterface $request Запрос, на который получен ответ.
     * @param HttpResponseInterface $response Ответ сервера.
     */
    public function __construct(HttpRequestInterface $request, HttpResponseInterface $response)
    {
        parent::__construct('Unexpected ContentType.', $request, $response);
    }
}
