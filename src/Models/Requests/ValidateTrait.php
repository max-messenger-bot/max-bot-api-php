<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\RequireArgumentException;
use MaxMessenger\Bot\Exceptions\Validation\MaxItemsException;
use MaxMessenger\Bot\Exceptions\Validation\MaxLengthException;
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
            throw new RequireArgumentException($argumentName);
        }
    }

    protected static function validateString(
        string $argumentName,
        ?string $value,
        ?int $minLength = null,
        ?int $maxLength = null
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
    }

}
