<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

final class MinLengthException extends ValidationException
{
    public function __construct(string $propNme, int $length, int $minLength)
    {
        $part1 = $minLength === 1
            ? 'a non-empty string'
            : sprintf('a string of at least %d characters', $minLength);
        $part2 = $length === 0
            ? 'an empty string'
            : sprintf('a string of %d characters', $length);

        parent::__construct(sprintf('Property "%s" expects %s, but %s was passed.', $propNme, $part1, $part2));
    }
}
