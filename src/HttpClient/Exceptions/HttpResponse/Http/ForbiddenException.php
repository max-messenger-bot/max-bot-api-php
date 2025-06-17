<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http;

/**
 * Ошибка доступа.
 *
 * У вас нет прав доступа к этому ресурсу.
 */
final class ForbiddenException extends MaxHttpException
{
}
