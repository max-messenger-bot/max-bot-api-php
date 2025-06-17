<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

final class MaxItemsException extends ValidationException
{
    public function __construct(string $argumentName, int $countItems, int $maxItems)
    {
        $part1 = sprintf('an array of up to %s items', $maxItems);
        $part2 = sprintf('an array of %s items', $countItems);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $part2));
    }
}
