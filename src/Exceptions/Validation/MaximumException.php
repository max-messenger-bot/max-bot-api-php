<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

/**
 * Argument exceeds maximum value.
 *
 * Exception thrown when a numeric argument exceeds the maximum allowed value.
 */
final class MaximumException extends ValidationException
{
    public function __construct(string $argumentName, int $value, int $minimum)
    {
        $part1 = sprintf('a number less than or equal to %d', $minimum);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $value));
    }
}
