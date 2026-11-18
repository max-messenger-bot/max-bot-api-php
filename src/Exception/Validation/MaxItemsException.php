<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use function sprintf;

/**
 * В массиве слишком много элементов.
 *
 * Возникает, если аргумент-массив содержит больше элементов, чем допустимо.
 */
final class MaxItemsException extends ValidationException
{
    /**
     * @param string $argumentName Имя аргумента-массива.
     * @param int $countItems Фактическое число элементов.
     * @param int $maxItems Максимально допустимое число элементов.
     */
    public function __construct(string $argumentName, int $countItems, int $maxItems)
    {
        $part1 = sprintf('an array of up to %s items', $maxItems);
        $part2 = sprintf('an array of %s items', $countItems);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $part2));
    }
}
