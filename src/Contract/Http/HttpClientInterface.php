<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract\Http;

use MaxMessenger\Bot\Contract\MaxHttpClientInterface;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\Exception\TimeoutException;

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
     * @throws TimeoutException Если ответ не получен из-за истечения времени ожидания.
     * @throws TemporaryNetworkException Если ответ не получен из-за временного сетевого сбоя.
     */
    public function request(HttpRequestInterface $request): HttpResponseInterface;
}
