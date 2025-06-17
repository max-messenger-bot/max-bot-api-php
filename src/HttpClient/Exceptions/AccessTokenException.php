<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions;

use MaxMessenger\Bot\Exceptions\LogicException;

/**
 * Access token not set.
 *
 * Exception thrown when the access token is not configured.
 */
final class AccessTokenException extends LogicException
{
    public function __construct()
    {
        parent::__construct('Access token not set.');
    }
}
