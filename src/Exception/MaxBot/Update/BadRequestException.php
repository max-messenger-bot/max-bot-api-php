<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Update;

use Throwable;

/**
 * Некорректный запрос.
 *
 * Возникает, если запрос с обновлением имеет недопустимый формат.
 */
final class BadRequestException extends UpdateRequestException
{
    /**
     * @param string $message Сообщение об ошибке в запросе с событием.
     * @param Throwable|null $previous Предыдущее исключение, если есть.
     */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}
