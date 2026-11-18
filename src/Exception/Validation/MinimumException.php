<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use function sprintf;

/**
 * Аргумент меньше минимального значения.
 *
 * Возникает, если числовой аргумент меньше допустимого минимума.
 */
final class MinimumException extends ValidationException
{
    /**
     * @param string $argumentName Имя числового аргумента.
     * @param int $value Фактическое значение аргумента.
     * @param int $minimum Минимально допустимое значение.
     */
    public function __construct(string $argumentName, int $value, int $minimum)
    {
        $part1 = sprintf('a number greater than or equal to %d', $minimum);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $value));
    }
}
