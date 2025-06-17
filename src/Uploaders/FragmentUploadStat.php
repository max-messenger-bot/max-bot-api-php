<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Uploaders;

/**
 * Статистика загрузки фрагмента.
 */
final readonly class FragmentUploadStat
{
    /**
     * @param non-negative-int $offset Смещение фрагмента от начала потока или файла.
     * @param non-negative-int $length Длина фрагмента.
     * @param non-negative-int $size Размер данных потока или файла.
     * @param float $time Время в секундах, за которое фрагмент был загружен.
     */
    public function __construct(
        public int $offset,
        public int $length,
        public int $size,
        public float $time
    ) {
    }
}
