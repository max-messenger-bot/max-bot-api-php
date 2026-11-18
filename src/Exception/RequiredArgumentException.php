<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception;

use InvalidArgumentException;

use function sprintf;

/**
 * Аргумент не может быть равен `null`.
 *
 * Возникает, если обязательный аргумент равен `null`.
 */
final class RequiredArgumentException extends InvalidArgumentException
{
    /**
     * @param string $argument Имя обязательного аргумента, переданного как `null`.
     */
    public function __construct(string $argument)
    {
        parent::__construct(sprintf('The $%s argument cannot be null.', $argument));
    }
}
