<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contracts\Uploaders;

use MaxMessenger\Bot\Uploaders\MaxUploader;

/**
 * Интерфейс для представления метаданных потока при загрузке файлов.
 *
 * Определяет контракт для получения информации о файловом потоке: имя файла, ресурс потока и размер данных.
 *
 * Используется загрузчиками {@see MaxUploader} для передачи файловых данных на сервер через HTTP-запросы.
 */
interface StreamInterface
{
    /**
     * @return non-empty-string Имя файла, используемое при загрузке.
     */
    public function getPostName(): string;

    /**
     * @return resource Ресурс PHP stream для чтения данных файла.
     */
    public function getResource(): mixed;

    /**
     * @return non-negative-int Размер потока в байтах.
     */
    public function getSize(): int;
}
