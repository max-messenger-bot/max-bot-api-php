<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions;

/**
 * Action prohibited.
 *
 * Exception thrown when an action is prohibited.
 */
final class ActionProhibited extends LogicException
{
    public function __construct()
    {
        parent::__construct('Action prohibited.');
    }
}
