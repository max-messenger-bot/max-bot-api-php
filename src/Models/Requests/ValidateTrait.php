<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\Validation\MaxItemsException;
use MaxMessenger\Bot\Exceptions\Validation\MaxLengthException;
use MaxMessenger\Bot\Exceptions\Validation\MinItemsException;
use MaxMessenger\Bot\Exceptions\Validation\MinLengthException;

use function count;

trait ValidateTrait
{
    /**
     * @var bool If validation is not working correctly, you can disable it by setting the value to `true`.
     */
    public static bool $disableValidation = false;

    protected static function validateArray(
        string $propName,
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
                throw new MinItemsException($propName, $countItems, $minItems);
            }

            if ($maxItems !== null && $countItems > $maxItems) {
                throw new MaxItemsException($propName, $countItems, $maxItems);
            }
        }
    }

    protected static function validateString(
        string $propName,
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
                throw new MinLengthException($propName, $length, $minLength);
            }

            if ($maxLength !== null && $length > $maxLength) {
                throw new MaxLengthException($propName, $length, $maxLength);
            }
        }
    }
}
