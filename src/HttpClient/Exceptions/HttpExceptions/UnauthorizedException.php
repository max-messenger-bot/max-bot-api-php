<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions\HttpExceptions;

/**
 * Authorization Error.
 *
 * No `access_token` provided or token is invalid.
 */
final class UnauthorizedException extends MaxHttpException
{
}
