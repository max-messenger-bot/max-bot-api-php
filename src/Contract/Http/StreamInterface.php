<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract\Http;

use RuntimeException;

use const SEEK_SET;

/**
 * Поток данных, из которого клиент читает тело запроса.
 */
interface StreamInterface
{
    /**
     * @return bool `true`, если поток кончился.
     */
    public function eof(): bool;

    /**
     * @return string Оставшееся содержимое потока от текущей позиции до конца.
     * @throws RuntimeException Если невозможно прочитать оставшееся содержимое целиком.
     */
    public function getContents(): string;

    /**
     * @return positive-int Размер потока в байтах.
     */
    public function getSize(): int;

    /**
     * @return bool `true`, если поток можно перематывать.
     */
    public function isSeekable(): bool;

    /**
     * Читает из потока очередную порцию данных.
     *
     * @param positive-int $length Сколько байт прочитать.
     * @return string Прочитанные данные: не больше `$length` байт и пустая строка, если поток кончился.
     * @throws RuntimeException Если прочитать данные не удалось.
     */
    public function read(int $length): string;

    /**
     * Перемещает текущую позицию в потоке.
     *
     * Для неперематываемого потока проверяет, совпадает ли текущая позиция с запрошенной.
     *
     * @param int $offset Смещение.
     * @param int $whence Откуда отсчитывать смещение: `SEEK_SET`, `SEEK_CUR` или `SEEK_END`, как у `fseek()`.
     * @throws RuntimeException Если позиция неперематываемого потока не совпадает с запрошенной
     *     или перемотка не удалась.
     */
    public function seek(int $offset, int $whence = SEEK_SET): void;

    /**
     * @return non-negative-int Текущая позиция в потоке.
     * @throws RuntimeException Если позицию определить не удалось.
     */
    public function tell(): int;
}
