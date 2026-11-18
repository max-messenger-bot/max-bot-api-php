<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use function sprintf;

/**
 * Строка слишком длинная.
 *
 * Возникает, если длина строкового аргумента превышает допустимый максимум.
 */
final class MaxLengthException extends ValidationException
{
    /**
     * @param string $argumentName Имя строкового аргумента.
     * @param int $length Фактическая длина строки.
     * @param int $maxLength Максимально допустимая длина строки.
     */
    public function __construct(string $argumentName, int $length, int $maxLength)
    {
        $part1 = sprintf('a string of up to %d characters', $maxLength);
        $part2 = sprintf('a string of %d characters', $length);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $part2));
    }
}
