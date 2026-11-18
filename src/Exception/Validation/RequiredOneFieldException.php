<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use function implode;
use function sprintf;

/**
 * Хотя бы одно поле должно отличаться от `null`.
 *
 * Возникает, если все перечисленные поля равны `null`.
 */
final class RequiredOneFieldException extends ValidationException
{
    /**
     * @param string[] $fieldNames Имена полей, среди которых хотя бы одно должно иметь значение.
     */
    public function __construct(array $fieldNames)
    {
        $fields = implode('", "', $fieldNames);

        parent::__construct(sprintf('At least one field of "%s" cannot be null.', $fields));
    }
}
