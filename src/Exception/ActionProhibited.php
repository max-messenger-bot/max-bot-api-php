<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception;

/**
 * Action prohibited.
 *
 * Exception thrown when an action is prohibited.
 */
final class ActionProhibited extends MaxApiLogicException
{
    public function __construct()
    {
        parent::__construct('Action prohibited.');
    }
}
