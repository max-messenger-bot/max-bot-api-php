<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions;

use InvalidArgumentException;

use function sprintf;

final class RequireArgumentException extends InvalidArgumentException
{
    public function __construct(string $argument)
    {
        parent::__construct(sprintf('The $%s argument cannot actually be null.', $argument));
    }
}
