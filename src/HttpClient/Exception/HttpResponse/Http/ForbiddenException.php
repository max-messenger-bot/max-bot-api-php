<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Ошибка доступа (HTTP 403).
 *
 * У вас нет прав доступа к этому ресурсу.
 */
final class ForbiddenException extends HttpException {}
