<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;

/**
 * Промежуточный сервер получил некорректный ответ от вышестоящего.
 */
final class BadGatewayException extends HttpException implements TemporaryExceptionInterface {}
