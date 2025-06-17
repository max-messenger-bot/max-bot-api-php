<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

final class MustBeLessException extends ValidationException
{
    public function __construct(string $argumentName1, string $argumentName2)
    {
        parent::__construct(sprintf('Argument "%s" must be less than argument "%s".', $argumentName1, $argumentName2));
    }
}
