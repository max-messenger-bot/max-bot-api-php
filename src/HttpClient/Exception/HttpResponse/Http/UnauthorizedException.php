<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Ошибка аутентификации (HTTP 401).
 *
 * Не предоставлен `access_token` или токен недействителен.
 */
final class UnauthorizedException extends HttpException {}
