<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception;

/**
 * Токен доступа не задан.
 *
 * Возникает, если токен доступа не настроен.
 */
final class AccessTokenException extends MaxApiException
{
    public function __construct()
    {
        parent::__construct('Access token not set.');
    }
}
