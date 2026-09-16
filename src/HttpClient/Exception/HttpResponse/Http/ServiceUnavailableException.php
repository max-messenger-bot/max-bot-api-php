<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;

/**
 * Сервис недоступен.
 */
final class ServiceUnavailableException extends HttpException implements TemporaryExceptionInterface {}
