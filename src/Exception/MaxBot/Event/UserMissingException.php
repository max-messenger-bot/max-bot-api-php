<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxApiLogicException;

/**
 * Пользователь отсутствует.
 *
 * Исключение выбрасывается, когда у события нет пользователя или его идентификатора.
 */
final class UserMissingException extends MaxApiLogicException
{
    public function __construct()
    {
        parent::__construct('User missing.');
    }
}
