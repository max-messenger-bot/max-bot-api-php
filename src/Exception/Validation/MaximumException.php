<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use function sprintf;

/**
 * Аргумент превышает максимальное значение.
 *
 * Возникает, если числовой аргумент превышает допустимый максимум.
 */
final class MaximumException extends ValidationException
{
    /**
     * @param string $argumentName Имя числового аргумента.
     * @param int $value Фактическое значение аргумента.
     * @param int $maximum Максимально допустимое значение.
     */
    public function __construct(string $argumentName, int $value, int $maximum)
    {
        $part1 = sprintf('a number less than or equal to %d', $maximum);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $value));
    }
}
