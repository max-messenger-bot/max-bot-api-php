<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\MaxBot\Events;

use MaxMessenger\Bot\Exceptions\LogicException;

/**
 * Служебное исключение для завершения обработки.
 *
 * Служебное исключение, позволяющее завершить работу текущего обработчика **событий** или **исключений** в любом месте.
 */
final class EventException extends LogicException
{
    private function __construct(
        public readonly ?bool $isHandled = null
    ) {
        parent::__construct('Event Internal Exception');
    }

    /**
     * Прервать обработку **события** или **исключения**, установить **событию** статус `обработано`.
     */
    public static function break(): never
    {
        throw new self(true);
    }

    /**
     * Прервать обработку **события** или **исключения**, установить **событию** статус `не обработано`.
     */
    public static function continue(): never
    {
        throw new self(false);
    }

    /**
     * Прервать обработку **события** или **исключения**, статус *событию** не менять.
     */
    public static function exit(): never
    {
        throw new self();
    }
}
