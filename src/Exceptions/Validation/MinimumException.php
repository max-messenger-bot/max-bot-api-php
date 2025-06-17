<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

/**
 * Argument is below minimum value.
 *
 * Exception thrown when a numeric argument is less than the minimum allowed value.
 */
final class MinimumException extends ValidationException
{
    public function __construct(string $argumentName, int $value, int $minimum)
    {
        $part1 = sprintf('a number greater than or equal to %d', $minimum);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $value));
    }
}
