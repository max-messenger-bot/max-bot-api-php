<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use ArrayIterator;
use DateTimeImmutable;
use MaxMessenger\Bot\Contracts\ModelInterface;
use MaxMessenger\Bot\Exceptions\ActionProhibited;
use Traversable;

abstract class BaseResponseModel implements ModelInterface
{
    final private function __construct(
        protected readonly array $data
    ) {
    }

    /**
     * Returns an iterator of the model's raw data.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Get raw model data.
     */
    final public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * Returns data that should be serialized into JSON format.
     */
    final public function jsonSerialize(): array
    {
        return $this->getRawData();
    }

    public static function newFromData(array $data): static
    {
        return new static($data);
    }

    public static function newFromNullableData(?array $data): ?static
    {
        return $data !== null
            ? static::newFromData($data)
            : null;
    }

    /**
     * @template T of array[][]
     * @param T $data
     * @return (
     *     T is non-empty-array<array<array>>
     *     ? (
     *         T is non-empty-array<non-empty-array<array>>
     *         ? non-empty-list<non-empty-list<static>>
     *         : non-empty-list<list<static>>
     *     )
     *     : list<list<static>>
     * )
     */
    public static function newList2DFromData(array $data): array
    {
        foreach ($data as &$value) {
            $value = static::newListFromData($value);
        }

        /** @var array<list<static>> $data */
        return array_values($data);
    }

    /**
     * @template T of array[]
     * @param T $data
     * @return (T is non-empty-array<array> ? non-empty-list<static> : list<static>)
     */
    public static function newListFromData(array $data): array
    {
        foreach ($data as &$value) {
            $value = static::newFromData($value);
        }

        /** @var static[] $data */
        return array_values($data);
    }

    /**
     * @param array[]|null $data
     * @return list<static>|null
     */
    public static function newListFromNullableData(?array $data): ?array
    {
        return $data !== null
            ? static::newListFromData($data)
            : null;
    }

    /**
     * Check if a property exists in raw data.
     */
    public function offsetExists(mixed $offset): bool
    {
        /** @var array-key|null $offset */
        return isset($this->data[$offset ?? '']);
    }

    /**
     * Get a property from raw data.
     */
    public function offsetGet(mixed $offset): mixed
    {
        /** @var array-key|null $offset */
        return $this->data[$offset ?? ''] ?? null;
    }

    /**
     * Set a property in raw data (prohibited).
     *
     * @internal
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ActionProhibited();
    }

    /**
     * Remove property from raw data (prohibited).
     *
     * @internal
     */
    final public function offsetUnset(mixed $offset): void
    {
        throw new ActionProhibited();
    }

    protected static function makeDateTime(int $timestamp): DateTimeImmutable
    {
        if (PHP_VERSION_ID >= 80400) {
            /**
             * @psalm-suppress UndefinedMethod, MixedReturnStatement
             */
            return DateTimeImmutable::createFromTimestamp($timestamp / 1000);
        }

        /** @var DateTimeImmutable */
        return DateTimeImmutable::createFromFormat('U.u', number_format($timestamp / 1000, 1, '.', ''));
    }

    protected static function makeNullableDateTime(?int $timestamp): ?DateTimeImmutable
    {
        return $timestamp !== null
            ? static::makeDateTime($timestamp)
            : null;
    }
}
