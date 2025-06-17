<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

use BackedEnum;

/**
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
        foreach ($values as &$value) {
            $value = static::tryFrom($value);
        }

        /** @var array<static|null> $values */
        return array_values(array_filter($values));
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
