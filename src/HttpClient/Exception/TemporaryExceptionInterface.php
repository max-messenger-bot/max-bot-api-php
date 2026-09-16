<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception;

use MaxMessenger\Bot\HttpClient\MaxHttpClient;
use Throwable;

/**
 * Временный сбой: тот же запрос имеет смысл повторить.
 *
 * Интерфейс реализуют ошибки транспорта {@see HttpClientException} и ответы сервера, которые говорят
 * о временной недоступности: `429`, `500`, `502`, `503` и `504`. Именно по нему
 * {@see MaxHttpClient} решает, повторять ли запрос.
 */
interface TemporaryExceptionInterface extends Throwable {}
