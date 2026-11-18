<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use function sprintf;

/**
 * Поле не может быть равно `null`.
 *
 * Возникает, если обязательное поле равно `null`.
 */
final class RequiredFieldException extends ValidationException
{
    /**
     * @param string $fieldName Имя обязательного поля, значение которого равно `null`.
     */
    public function __construct(string $fieldName)
    {
        parent::__construct(sprintf('The "%s" field cannot be null.', $fieldName));
    }
}
