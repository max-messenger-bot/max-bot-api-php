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
     * @param array<string|int> $values
     * @return list<static>
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
     * @param array<string|int>|null $values
     * @return list<static>|null
     */
    public static function tryFromNullableList(?array $values): ?array
    {
        return $values !== null
            ? static::tryFromList($values)
            : null;
    }
}
