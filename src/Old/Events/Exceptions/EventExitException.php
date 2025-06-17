<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Events\Exceptions;

use RuntimeException;

final class EventExitException extends RuntimeException
{
    public function __construct(bool $isContinue)
    {
        parent::__construct($isContinue ? 'continue' : 'break', (int)$isContinue);
    }
}
