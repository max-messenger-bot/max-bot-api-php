<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

final class MaxLengthException extends ValidationException
{
    public function __construct(string $argumentName, int $length, int $maxLength)
    {
        $part1 = sprintf('a string of up to "%d" characters', $maxLength);
        $part2 = sprintf('a string of %d characters', $length);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $part2));
    }
}
