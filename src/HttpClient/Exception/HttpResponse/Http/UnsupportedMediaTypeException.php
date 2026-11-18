<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Неподдерживаемый тип данных (HTTP 415).
 */
final class UnsupportedMediaTypeException extends HttpException {}
