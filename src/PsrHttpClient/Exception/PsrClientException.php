<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\PsrHttpClient\Exception;

use MaxMessenger\Bot\HttpClient\Exception\HttpClientException;

/**
 * Ошибка клиента PSR-18, не связанная с сетью.
 *
 * Оборачивает исключение стороннего клиента: например, запрос не удалось отправить из-за его собственных
 * ограничений. Исходное исключение доступно через `getPrevious()`.
 */
final class PsrClientException extends HttpClientException {}
