<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions\HttpExceptions;

/**
 * Access error.
 *
 * You don't have permissions to access this resource.
 */
final class ForbiddenException extends MaxHttpException
{
}
