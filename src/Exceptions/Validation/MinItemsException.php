<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

final class MinItemsException extends ValidationException
{
    public function __construct(string $argumentName, int $countItems, int $minItems)
    {
        $part1 = $minItems === 1
            ? 'a non-empty array'
            : sprintf('an array of at least %d items', $minItems);
        $part2 = $countItems === 0
            ? 'an empty array'
            : sprintf('an array of %s items', $countItems);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $part2));
    }
}
