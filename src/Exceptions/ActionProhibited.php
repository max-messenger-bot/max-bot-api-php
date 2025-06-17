<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions;

final class ActionProhibited extends LogicException
{
    public function __construct()
    {
        parent::__construct('Action prohibited.');
    }
}
