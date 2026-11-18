<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Запрошенный диапазон не может быть предоставлен (HTTP 416).
 */
final class RangeNotSatisfiableException extends HttpException {}
