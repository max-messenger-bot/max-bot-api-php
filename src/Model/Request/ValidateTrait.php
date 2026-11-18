<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Exception\RequiredArgumentException;
use MaxMessenger\Bot\Exception\Validation\MatchException;
use MaxMessenger\Bot\Exception\Validation\MaximumException;
use MaxMessenger\Bot\Exception\Validation\MaxItemsException;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinimumException;
use MaxMessenger\Bot\Exception\Validation\MinItemsException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;

use function count;
use function mb_strlen;
use function preg_match;

/**
 * Проверяет аргументы методов моделей запросов и клиента API.
 */
trait ValidateTrait
{
    /**
     * @var bool `true` Отключает проверки аргументов методов. Свойство объявлено в трейте, поэтому значение
     *     задаётся отдельно для каждого класса, использующего трейт (и наследуется его потомками).
     *     Проверки обязательных полей при сериализации не отключаются.
     */
    public static bool $disableValidation = false;

    /**
     * Проверяет версию API.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param string|null $value Проверяемая версия API.
     */
    protected static function validateApiVersion(string $argumentName, ?string $value): void
    {
        if (static::$disableValidation) {
            return;
        }

        if (!preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $value ?? '')) {
            throw new MatchException($argumentName);
        }
    }

    /**
     * Проверяет количество элементов массива, если значение передано.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param array|null $value Значение аргумента или `null`.
     * @param int|null $minItems Минимальное количество элементов.
     * @param int|null $maxItems Максимальное количество элементов.
     */
    protected static function validateArray(
        string $argumentName,
        ?array $value,
        ?int $minItems = null,
        ?int $maxItems = null,
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
     * Проверяет количество элементов во вложенных массивах.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param array[]|null $value Двумерный массив или `null`.
     * @param int|null $minItems Минимальное количество элементов в каждом вложенном массиве.
     * @param int|null $maxItems Максимальное количество элементов в каждом вложенном массиве.
     */
    protected static function validateArray2D(
        string $argumentName,
        ?array $value,
        ?int $minItems = null,
        ?int $maxItems = null,
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

    /**
     * Проверяет идентификатор нажатия callback-кнопки.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param string|null $value Проверяемый идентификатор.
     */
    protected static function validateCallbackId(string $argumentName, ?string $value): void
    {
        if (static::$disableValidation) {
            return;
        }

        if (!preg_match('/^\s*\S[\s\S]*$/', $value ?? '')) {
            throw new MatchException($argumentName);
        }
    }

    /**
     * Проверяет целое число на попадание в допустимые границы.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param int|null $value Значение аргумента или `null`.
     * @param int|null $minimum Минимальное допустимое значение.
     * @param int|null $maximum Максимальное допустимое значение.
     */
    protected static function validateInt(
        string $argumentName,
        ?int $value,
        ?int $minimum = null,
        ?int $maximum = null,
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

    /**
     * Проверяет идентификатор сообщения, поста или комментария.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param string|null $value Проверяемый идентификатор (`mid`).
     */
    protected static function validateMid(string $argumentName, ?string $value): void
    {
        if (static::$disableValidation) {
            return;
        }

        if (!preg_match('/^mid\.[a-zA-Z0-9_-]+$/', $value ?? '')) {
            throw new MatchException($argumentName);
        }
    }

    /**
     * Проверяет, что значение обязательного аргумента передано.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param mixed $value Значение аргумента.
     * @psalm-assert !null $value
     */
    protected static function validateNotNull(string $argumentName, mixed $value): void
    {
        if (!static::$disableValidation && $value === null) {
            throw new RequiredArgumentException($argumentName);
        }
    }

    /**
     * Проверяет длину строки и соответствие шаблону.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param string|null $value Значение аргумента или `null`.
     * @param int|null $minLength Минимальная длина строки.
     * @param int|null $maxLength Максимальная длина строки.
     * @param non-empty-string|null $pattern Регулярное выражение для проверки строки.
     */
    protected static function validateString(
        string $argumentName,
        ?string $value,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null,
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

    /**
     * Проверяет адрес подписки Webhook.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param string|null $value Проверяемый адрес подписки.
     */
    protected static function validateSubscriptionUrl(string $argumentName, ?string $value): void
    {
        self::validateString($argumentName, $value, minLength: 12);
    }

    /**
     * Проверяет токен видеовложения.
     *
     * @param string $argumentName Имя проверяемого аргумента.
     * @param string|null $value Проверяемый токен.
     */
    protected static function validateVideoToken(string $argumentName, ?string $value): void
    {
        if (static::$disableValidation) {
            return;
        }

        if (!preg_match('/^vid\.[\x21-\x7E]+$/', $value ?? '')) {
            throw new MatchException($argumentName);
        }
    }
}
