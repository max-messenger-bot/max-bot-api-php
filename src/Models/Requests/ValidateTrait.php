<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\RequiredArgumentException;
use MaxMessenger\Bot\Exceptions\Validation\MatchException;
use MaxMessenger\Bot\Exceptions\Validation\MaximumException;
use MaxMessenger\Bot\Exceptions\Validation\MaxItemsException;
use MaxMessenger\Bot\Exceptions\Validation\MaxLengthException;
use MaxMessenger\Bot\Exceptions\Validation\MinimumException;
use MaxMessenger\Bot\Exceptions\Validation\MinItemsException;
use MaxMessenger\Bot\Exceptions\Validation\MinLengthException;
use MaxMessenger\Bot\Exceptions\Validation\MustBeLessException;

use function count;

trait ValidateTrait
{
    /**
     * @var bool If validation is not working correctly, you can disable it by setting the value to `true`.
     */
    public static bool $disableValidation = false;

    protected static function validateArray(
        string $argumentName,
        ?array $value,
        ?int $minItems = null,
        ?int $maxItems = null
    ): void {
        if (static::$disableValidation || $value === null) {
            return;
        }

        if ($minItems !== null || $maxItems !== null) {
            $countItems = count($value);

            if ($minItems !== null && $countItems < $minItems) {
                throw new MinItemsException($argumentName, $countItems, $minItems);
            }

            if ($maxItems !== null && $countItems > $maxItems) {
                throw new MaxItemsException($argumentName, $countItems, $maxItems);
            }
        }
    }

    /**
     * @param array[]|null $value
     */
    protected static function validateArray2D(
        string $argumentName,
        ?array $value,
        ?int $minItems = null,
        ?int $maxItems = null
    ): void {
        if (static::$disableValidation || $value === null) {
            return;
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        foreach ($value as $itemKey => $item) {
            $argumentName2 = "{$argumentName}[$itemKey]";
            self::validateNotNull($argumentName2, $item);
            self::validateArray($argumentName2, $item, $minItems, $maxItems);
        }
    }

    protected static function validateInt(
        string $argumentName,
        ?int $value,
        ?int $minimum = null,
        ?int $maximum = null
    ): void {
        if (static::$disableValidation || $value === null) {
            return;
        }

        if ($minimum !== null && $value < $minimum) {
            throw new MinimumException($argumentName, $value, $minimum);
        }

        if ($maximum !== null && $value > $maximum) {
            throw new MaximumException($argumentName, $value, $maximum);
        }
    }

    protected static function validateMustBeLess(
        string $argument1Name,
        string $argument2Name,
        bool $conditionIsMet
    ): void {
        if (!static::$disableValidation && !$conditionIsMet) {
            throw new MustBeLessException($argument1Name, $argument2Name);
        }
    }

    /**
     * @psalm-assert !null $value
     */
    protected static function validateNotNull(string $argumentName, mixed $value): void
    {
        if (!static::$disableValidation && $value === null) {
            throw new RequiredArgumentException($argumentName);
        }
    }

    /**
     * @param non-empty-string|null $pattern
     */
    protected static function validateString(
        string $argumentName,
        ?string $value,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null
    ): void {
        if (static::$disableValidation || $value === null) {
            return;
        }

        if ($minLength !== null || $maxLength !== null) {
            $length = mb_strlen($value, 'UTF-8');

            if ($minLength !== null && $length < $minLength) {
                throw new MinLengthException($argumentName, $length, $minLength);
            }

            if ($maxLength !== null && $length > $maxLength) {
                throw new MaxLengthException($argumentName, $length, $maxLength);
            }
        }

        if ($pattern !== null && !preg_match($pattern, $value)) {
            throw new MatchException($argumentName);
        }
    }
}
