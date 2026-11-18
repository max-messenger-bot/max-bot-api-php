<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpRequest;

use MaxMessenger\Bot\Exception\MaxApiException;

/**
 * Базовый класс ошибок подготовки запроса.
 *
 * Запрос не отправлен: собрать его не удалось, поэтому ни запроса, ни ответа у ошибки нет.
 */
abstract class HttpRequestException extends MaxApiException {}
