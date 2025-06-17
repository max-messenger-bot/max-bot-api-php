<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

final class MaxItemsException extends ValidationException
{
    public function __construct(string $propNme, int $countItems, int $maxItems)
    {
        $part1 = sprintf('an array of up to %s items', $maxItems);
        $part2 = sprintf('an array of %s items', $countItems);

        parent::__construct(sprintf('Property "%s" expects %s, but %s was passed.', $propNme, $part1, $part2));
    }
}
