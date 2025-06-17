<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use ArrayIterator;
use DateTimeImmutable;
use MaxMessenger\Bot\Contracts\ModelInterface;
use MaxMessenger\Bot\Exceptions\ActionProhibited;
use Traversable;

abstract readonly class BaseResponseModel implements ModelInterface
{
    final private function __construct(
        protected array $data
    ) {
    }

    /**
     * Returns an iterator of the model's raw data.
     *
     * @api
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Get raw model data.
     *
     * @api
     */
    final public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * Returns data that should be serialized into JSON format.
     *
     * @api
     */
    final public function jsonSerialize(): array
    {
        return $this->getRawData();
    }

    /**
     * @internal
     */
    public static function newFromData(array $data): static
    {
        return new static($data);
    }

    /**
     * @internal
     */
    public static function newFromNullableData(?array $data): ?static
    {
        return $data !== null
            ? new static($data)
            : null;
    }

    /**
     * @param array[][] $data
     * @return list<list<static>>
     * @internal
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
     * @param array[] $data
     * @return list<static>
     * @internal
     */
    public static function newListFromData(array $data): array
    {
        foreach ($data as &$value) {
            $value = new static($value);
        }

        /** @var static[] $data */
        return array_values($data);
    }

    /**
     * @param array[]|null $data
     * @return list<static>|null
     * @internal
     */
    public static function newListFromNullableData(?array $data): ?array
    {
        return $data !== null
            ? static::newListFromData($data)
            : null;
    }

    /**
     * Check if a property exists in raw data.
     *
     * @api
     */
    public function offsetExists(mixed $offset): bool
    {
        /** @psalm-suppress MixedArrayOffset */
        return isset($this->data[$offset]);
    }

    /**
     * Get a property from raw data.
     *
     * @api
     */
    public function offsetGet(mixed $offset): mixed
    {
        /** @psalm-suppress MixedArrayOffset */
        return $this->data[$offset] ?? null;
    }

    /**
     * Set a property in raw data (prohibited).
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ActionProhibited();
    }

    /**
     * Remove property from raw data (prohibited).
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
        return DateTimeImmutable::createFromFormat('U', (string)($timestamp / 1000));
    }
}
