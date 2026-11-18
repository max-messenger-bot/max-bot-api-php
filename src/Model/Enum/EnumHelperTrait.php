<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

use BackedEnum;

/**
 * Трейт с вспомогательными методами для перечислений.
 *
 * @psalm-require-implements BackedEnum
 */
trait EnumHelperTrait
{
    /**
     * Преобразует известные значения в элементы перечисления, пропуская неизвестные.
     *
     * @param array<string|int> $values Значения элементов перечисления.
     * @return list<static> Найденные элементы в порядке исходного списка.
     */
    public static function tryFromList(array $values): array
    {
        $result = [];
        foreach ($values as $value) {
            $enum = static::tryFrom($value);
            if ($enum !== null) {
                $result[] = $enum;
            }
        }

        return $result;
    }

    /**
     * Преобразует список значений, если он передан.
     *
     * @param array<string|int>|null $values Значения элементов перечисления или `null`.
     * @return list<static>|null Найденные элементы или `null`, если список не передан.
     */
    public static function tryFromNullableList(?array $values): ?array
    {
        return $values !== null
            ? static::tryFromList($values)
            : null;
    }
}
