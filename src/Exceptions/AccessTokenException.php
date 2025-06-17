<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Exceptions;

use LogicException;

final class AccessTokenException extends LogicException
{
    public function __construct()
    {
        parent::__construct('Access token not set.');
    }
}
