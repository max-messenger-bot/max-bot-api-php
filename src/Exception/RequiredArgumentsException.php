<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception;

use InvalidArgumentException;

/**
 * Хотя бы один аргумент должен отличаться от `null`.
 *
 * Возникает, если все аргументы из обязательного набора равны `null`.
 */
final class RequiredArgumentsException extends InvalidArgumentException
{
    /**
     * @param string $message Сообщение об ошибке или пустая строка для сообщения по умолчанию.
     */
    public function __construct(string $message = '')
    {
        parent::__construct($message !== '' ? $message : 'At least one argument must be non-null.');
    }
}
