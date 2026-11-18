<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;

/**
 * Промежуточный сервер не дождался ответа от вышестоящего (HTTP 504).
 */
final class GatewayTimeoutException extends HttpException implements TemporaryExceptionInterface {}
