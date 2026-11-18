<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

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
     * @param mixed $offset Имя или индекс поля.
     * @return mixed Значение поля или `null`, если его нет.
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function offsetGet(mixed $offset): mixed
    {
        /** @var array-key|null $offset */
        if (!array_key_exists($offset ??= '', $this->data)) {
            return null;
        }

        $_value = &$this->data[$offset];
        if (is_array($_value)) {
            if ($_value) {
                $this->convertToRawModelArr($_value);
            }
        } elseif ($_value instanceof BaseRequestModel && !($_value instanceof self)) {
            $_value = $_value->getRawModel();
        }

        return $_value;
    }

    /**
     * Записывает поле в исходные данные запроса.
     *
     * @param mixed $offset Имя или индекс поля.
     * @param mixed $value Значение поля.
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        /** @var array-key|null $offset */
        $this->data[$offset ?? ''] = $value;
    }

    /**
     * Удаляет поле из исходных данных запроса.
     *
     * @param mixed $offset Имя или индекс поля.
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function offsetUnset(mixed $offset): void
    {
        /** @var array-key|null $offset */
        unset($this->data[$offset ?? '']);
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
        foreach ($arr as &$_value) {
            if (is_array($_value)) {
                $this->convertToRawModelArr($_value);
            } elseif ($_value instanceof BaseRequestModel && !($_value instanceof self)) {
                $_value = $_value->getRawModel();
            }
        }
    }
}
