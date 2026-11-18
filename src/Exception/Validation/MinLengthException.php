<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use function sprintf;

/**
 * Строка слишком короткая.
 *
 * Возникает, если длина строкового аргумента меньше допустимого минимума.
 */
final class MinLengthException extends ValidationException
{
    /**
     * @param string $argumentName Имя строкового аргумента.
     * @param int $length Фактическая длина строки.
     * @param int $minLength Минимально допустимая длина строки.
     */
    public function __construct(string $argumentName, int $length, int $minLength)
    {
        $part1 = $minLength === 1
            ? 'a non-empty string'
            : sprintf('a string of at least %d characters', $minLength);
        $part2 = $length === 0
            ? 'an empty string'
            : sprintf('a string of %d characters', $length);

        parent::__construct(sprintf('Argument "%s" expects %s, but %s was passed.', $argumentName, $part1, $part2));
    }
}
