<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse;

use JsonException;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;

/**
 * Ошибка разбора JSON.
 *
 * Тело ответа не удалось декодировать.
 */
final class JsonDecodeException extends ParseDataException
{
    /**
     * @param HttpRequestInterface $request Запрос, на который получен ответ.
     * @param HttpResponseInterface $response Ответ сервера.
     * @param JsonException|null $previous Исходная ошибка разбора.
     */
    public function __construct(
        HttpRequestInterface $request,
        HttpResponseInterface $response,
        ?JsonException $previous = null,
    ) {
        parent::__construct('JSON decode error.', $request, $response, $previous);
    }
}
