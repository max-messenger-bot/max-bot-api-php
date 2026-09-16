<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse;

use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;

/**
 * Неожиданный формат ответа.
 *
 * Тело ответа разобрано, но его структура не соответствует ожидаемой.
 */
final class UnexpectedFormatException extends ParseDataException
{
    /**
     * @param HttpRequestInterface $request Запрос, на который получен ответ.
     * @param HttpResponseInterface $response Ответ сервера.
     */
    public function __construct(HttpRequestInterface $request, HttpResponseInterface $response)
    {
        parent::__construct('Unexpected Response Format.', $request, $response);
    }
}
