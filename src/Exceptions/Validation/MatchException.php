<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

/**
 * Argument has an invalid value.
 *
 * Exception thrown when an argument does not match the expected pattern or value.
 */
final class MatchException extends ValidationException
{
    public function __construct(string $argumentName)
    {
        parent::__construct(sprintf('Argument "%s" has an invalid value.', $argumentName));
    }
}
