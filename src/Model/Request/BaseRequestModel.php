<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use ArrayIterator;
use MaxMessenger\Bot\Contract\ModelInterface;
use MaxMessenger\Bot\Exception\ActionProhibited;
use Traversable;

/**
 * Базовая модель тела запроса с сериализацией полей для API.
 */
abstract class BaseRequestModel implements ModelInterface
{
    /**
     * @var array Поля тела запроса, подготовленные для сериализации.
     */
    protected array $data = [];

    /**
     * @return Traversable<array-key, mixed> Итератор по исходным данным модели.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @return array Исходные данные модели.
     */
    final public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * @return RawModel Raw модель с исходными данными запроса.
     */
    final public function getRawModel(): RawModel
    {
        return $this instanceof RawModel
            ? $this
            : new RawModel($this->data);
    }

    /**
     * Возвращает данные модели для сериализации в JSON.
     *
     * @return object Поля тела запроса.
     */
    final public function jsonSerialize(): object
    {
        $this->validateRequired();

        return (object) $this->getRawData();
    }

    /**
     * Создаёт экземпляр класса.
     *
     * В отличие от `make()`, метод `new()` позволяет создать объект без передачи обязательных параметров.
     */
    abstract public static function new(): self;

    /**
     * Проверяет наличие поля в исходных данных запроса.
     *
     * @param array-key $offset Имя или индекс поля.
     * @return bool Есть ли поле в исходных данных.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * Возвращает поле из исходных данных запроса.
     *
     * @param array-key $offset Имя или индекс поля.
     * @return mixed Значение поля или `null`, если его нет.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * Отклоняет попытку изменить исходные данные запроса через доступ по индексу.
     *
     * @param array-key|null $offset Имя или индекс поля.
     * @param mixed $value Новое значение поля.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ActionProhibited();
    }

    /**
     * Отклоняет попытку удалить поле исходных данных запроса через доступ по индексу.
     *
     * @param array-key $offset Имя или индекс поля.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new ActionProhibited();
    }

    /**
     * Точка расширения для проверки обязательных полей модели при сериализации.
     */
    public function validateRequired(): void {}
}
