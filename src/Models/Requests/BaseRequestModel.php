<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use ArrayIterator;
use MaxMessenger\Bot\Contracts\ModelInterface;
use MaxMessenger\Bot\Exceptions\ActionProhibited;
use Traversable;

/**
 * @template T of array
 */
abstract class BaseRequestModel implements ModelInterface
{
    /**
     * @param T $data
     */
    protected array $data = [];

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
     * Set the model's raw data.
     *
     * @return T
     * @api
     */
    final public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * Get raw model data.
     *
     * @api
     */
    final public function getRawModel(): RawData
    {
        return new RawData($this->data);
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
     * @api
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * Check if a property exists in raw data.
     *
     * @api
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * Get a property from raw data.
     *
     * @api
     */
    public function offsetGet(mixed $offset): mixed
    {
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
    public function offsetUnset(mixed $offset): void
    {
        throw new ActionProhibited();
    }
}
