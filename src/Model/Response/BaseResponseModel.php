<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use ArrayIterator;
use DateTimeImmutable;
use MaxMessenger\Bot\Contract\ModelInterface;
use MaxMessenger\Bot\Exception\ActionProhibited;
use Traversable;

use function intdiv;
use function sprintf;

/**
 * Базовая модель ответа API с доступом к исходным данным.
 */
abstract class BaseResponseModel implements ModelInterface
{
    final private function __construct(
        protected readonly array $data,
    ) {}

    /**
     * @return Traversable<array-key, mixed> Итератор по исходным данным ответа.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @return array Исходные данные ответа.
     */
    final public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * Возвращает данные модели для сериализации в JSON.
     *
     * @return array Исходные данные ответа.
     */
    final public function jsonSerialize(): array
    {
        return $this->getRawData();
    }

    /**
     * Создаёт модель из данных ответа API.
     *
     * @param array $data Данные ответа API.
     */
    public static function newFromData(array $data): static
    {
        return new static($data);
    }

    /**
     * Создаёт модель, если данные ответа переданы.
     *
     * @param array|null $data Данные ответа API или `null`.
     * @return static|null Созданная модель или `null`.
     */
    public static function newFromNullableData(?array $data): ?static
    {
        return $data !== null
            ? static::newFromData($data)
            : null;
    }

    /**
     * Создаёт двумерный список моделей из данных ответа.
     *
     * @template T of array[][]
     * @param T $data Двумерный список данных моделей.
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
        $result = [];
        foreach ($data as $value) {
            $result[] = static::newListFromData($value);
        }

        return $result;
    }

    /**
     * Создаёт список моделей из данных ответа.
     *
     * @template T of array[]
     * @param T $data Список данных моделей.
     * @return (T is non-empty-array<array> ? non-empty-list<static> : list<static>)
     */
    public static function newListFromData(array $data): array
    {
        $result = [];
        foreach ($data as $value) {
            $result[] = static::newFromData($value);
        }

        return $result;
    }

    /**
     * Создаёт список моделей, если данные ответа переданы.
     *
     * @template T of array[]|null
     * @param T $data Список данных моделей или `null`.
     * @return (T is null ? null : (T is non-empty-array<array> ? non-empty-list<static> : list<static>))
     */
    public static function newListFromNullableData(?array $data): ?array
    {
        return $data !== null
            ? static::newListFromData($data)
            : null;
    }

    /**
     * Проверяет наличие поля в исходных данных ответа.
     *
     * @param mixed $offset Имя или индекс поля.
     * @return bool Есть ли поле в исходных данных.
     */
    public function offsetExists(mixed $offset): bool
    {
        /** @var array-key|null $offset */
        return isset($this->data[$offset ?? '']);
    }

    /**
     * Возвращает поле из исходных данных ответа.
     *
     * @param mixed $offset Имя или индекс поля.
     * @return mixed Значение поля или `null`, если его нет.
     */
    public function offsetGet(mixed $offset): mixed
    {
        /** @var array-key|null $offset */
        return $this->data[$offset ?? ''] ?? null;
    }

    /**
     * Отклоняет попытку изменить исходные данные ответа.
     *
     * @param mixed $offset Имя или индекс поля.
     * @param mixed $value Новое значение поля.
     * @internal
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ActionProhibited();
    }

    /**
     * Отклоняет попытку удалить поле из исходных данных ответа.
     *
     * @param mixed $offset Имя или индекс поля.
     * @internal
     */
    final public function offsetUnset(mixed $offset): void
    {
        throw new ActionProhibited();
    }

    /**
     * @param non-negative-int $timestamp Время (Unix-время в миллисекундах).
     */
    protected static function makeDateTime(int $timestamp): DateTimeImmutable
    {
        if (PHP_VERSION_ID >= 80400) {
            /**
             * @psalm-suppress UndefinedMethod, MixedReturnStatement
             */
            return DateTimeImmutable::createFromTimestamp($timestamp / 1000);
        }

        /** @psalm-suppress FalsableReturnStatement */
        return DateTimeImmutable::createFromFormat(
            'U.u',
            sprintf('%d.%06d', intdiv($timestamp, 1000), ($timestamp % 1000) * 1000),
        );
    }

    /**
     * @param non-negative-int|null $timestamp Время (Unix-время в миллисекундах).
     */
    protected static function makeNullableDateTime(?int $timestamp): ?DateTimeImmutable
    {
        return $timestamp !== null
            ? static::makeDateTime($timestamp)
            : null;
    }
}
