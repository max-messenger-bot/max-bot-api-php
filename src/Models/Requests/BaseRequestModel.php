<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use ArrayIterator;
use MaxMessenger\Bot\Contracts\ModelInterface;
use MaxMessenger\Bot\Exceptions\ActionProhibited;
use Traversable;

abstract class BaseRequestModel implements ModelInterface
{
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
     * Get raw model data.
     *
     * @api
     */
    final public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * Get RawData model.
     *
     * @api
     */
    final public function getRawModel(): RawModel
    {
        return new RawModel($this->data);
    }

    /**
     * Returns data that should be serialized into JSON format.
     *
     * @api
     */
    final public function jsonSerialize(): object
    {
        return (object)$this->getRawData();
    }

    /**
     * @api
     */
    public static function new(): static
    {
        /** @psalm-suppress UnsafeInstantiation */
        return new static();
    }

    /**
     * Check if a property exists in raw data.
     *
     * @api
     */
    public function offsetExists(mixed $offset): bool
    {
        /** @psalm-suppress MixedArrayOffset, MixedArrayTypeCoercion */
        return isset($this->data[$offset ?? '']);
    }

    /**
     * Get a property from raw data.
     *
     * @api
     */
    public function offsetGet(mixed $offset): mixed
    {
        /** @psalm-suppress MixedArrayOffset, MixedArrayTypeCoercion */
        return $this->data[$offset ?? ''] ?? null;
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
