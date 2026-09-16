<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;

/**
 * Превышено количество запросов.
 */
final class TooManyRequestsException extends HttpException implements TemporaryExceptionInterface {}
