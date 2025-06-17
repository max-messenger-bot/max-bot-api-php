<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions;

final class ValidateException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Validation failed.');
    }
}
