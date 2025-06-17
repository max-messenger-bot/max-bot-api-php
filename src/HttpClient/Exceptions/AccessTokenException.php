<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions;

use MaxMessenger\Bot\Exceptions\LogicException;

final class AccessTokenException extends LogicException
{
    public function __construct()
    {
        parent::__construct('Access token not set.');
    }
}
