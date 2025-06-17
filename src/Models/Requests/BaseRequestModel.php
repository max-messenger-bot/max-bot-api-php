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
     * Get RawData model.
     */
    final public function getRawModel(): RawModel
    {
        return new RawModel($this->data);
    }

    /**
     * Returns data that should be serialized into JSON format.
     */
    final public function jsonSerialize(): object
    {
        $this->validateRequired();

        return (object)$this->getRawData();
    }

    /**
     * Создаёт экземпляр класса.
     *
     * Используйте данный метод, если хотите создать пустой объект и наполнять его используя отдельные методы.
     *
     * Используйте метод `make()`, что бы установить все обязательные параметры сразу.
     *
     * Метод `new()` в отличие от `make()`, не требует передачи обязательных параметров сразу.
     */
    abstract public static function new(): self;

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

    public function validateRequired(): void
    {
    }
}
