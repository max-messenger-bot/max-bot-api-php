<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http;

/**
 * Ошибка аутентификации.
 *
 * Не предоставлен `access_token` или токен недействителен.
 */
final class UnauthorizedException extends MaxHttpException
{
}
