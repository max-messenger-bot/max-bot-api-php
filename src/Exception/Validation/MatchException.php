<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use function sprintf;

/**
 * Недопустимое значение аргумента.
 *
 * Возникает, если аргумент не соответствует ожидаемому шаблону или значению.
 */
final class MatchException extends ValidationException
{
    /**
     * @param string $argumentName Имя аргумента с недопустимым значением.
     */
    public function __construct(string $argumentName)
    {
        parent::__construct(sprintf('Argument "%s" has an invalid value.', $argumentName));
    }
}
