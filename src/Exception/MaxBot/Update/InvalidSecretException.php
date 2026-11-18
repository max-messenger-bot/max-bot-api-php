<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Update;

/**
 * Недействительный секретный ключ.
 *
 * Возникает, если передан неверный секретный ключ.
 */
final class InvalidSecretException extends UpdateRequestException
{
    public function __construct()
    {
        parent::__construct('Invalid Secret.', 403);
    }
}
