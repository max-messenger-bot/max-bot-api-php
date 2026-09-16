<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract\Http;

use MaxMessenger\Bot\Contract\MaxHttpClientInterface;

/**
 * HTTP-клиент, выполняющий запросы.
 *
 * Связывает {@see MaxHttpClientInterface} с транспортом: curl или PSR-18.
 */
interface HttpClientInterface
{
    /**
     * Выполняет запрос и возвращает ответ.
     *
     * @param HttpRequestInterface $request Запрос.
     * @return HttpResponseInterface Ответ сервера.
     */
    public function request(HttpRequestInterface $request): HttpResponseInterface;
}
