<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Exception\RequiredArgumentException;

use function array_key_exists;
use function is_array;

/**
 * Модель запроса для передачи данных без проверки их структуры.
 *
 * > [!CAUTION]
 * > Не используйте этот класс, если не знакомы с форматом данных API платформы МАКС.
 * > Не используйте этот класс без достаточного опыта программирования.
 * > Вы полностью отвечаете за корректность переданных данных.
 * > Неверная структура данных может привести к некорректному запросу и явным или скрытым ошибкам.
 */
class RawModel extends BaseRequestModel
{
    /**
     * @param array $data Данные для передачи в запросе API без проверки их структуры.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param array $data Данные для передачи в запросе API без проверки их структуры.
     */
    public static function new(array $data = []): BaseRequestModel
    {
        return new self($data);
    }

    /**
     * Возвращает поле исходных данных, преобразуя вложенные модели запроса.
     *
     * @param array-key $offset Имя или индекс поля.
     * @return mixed Значение поля или `null`, если его нет.
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!array_key_exists($offset, $this->data)) {
            return null;
        }

        $value = &$this->data[$offset];
        if (is_array($value)) {
            if ($value) {
                $this->convertToRawModelArr($value);
            }
        } elseif ($value instanceof BaseRequestModel && !($value instanceof self)) {
            $value = $value->getRawModel();
        }

        return $value;
    }

    /**
     * Записывает поле в исходные данные запроса.
     *
     * @param array-key|null $offset Имя или индекс поля.
     * @param mixed $value Значение поля.
     * @throws RequiredArgumentException Если ключ равен `null`, в том числе при записи через `$model[]`.
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            throw new RequiredArgumentException('offset');
        }

        $this->data[$offset] = $value;
    }

    /**
     * Удаляет поле из исходных данных запроса.
     *
     * @param array-key $offset Имя или индекс поля.
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @param array $data Новые исходные данные запроса.
     * @return $this
     */
    public function setRawData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    private function convertToRawModelArr(array &$arr): void
    {
        /** @var mixed $value */
        foreach ($arr as &$value) {
            if (is_array($value)) {
                $this->convertToRawModelArr($value);
            } elseif ($value instanceof BaseRequestModel && !($value instanceof self)) {
                $value = $value->getRawModel();
            }
        }
    }
}
