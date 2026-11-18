<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception;

/**
 * Действие запрещено.
 *
 * Возникает при попытке выполнить запрещённое действие.
 */
final class ActionProhibited extends MaxApiLogicException
{
    public function __construct()
    {
        parent::__construct('Action prohibited.');
    }
}
