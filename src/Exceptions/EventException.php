<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions;

/**
 * Служебное исключение для завершения обработки.
 *
 * Служебное исключение, позволяющее завершить работу текущего обработчика **событий** или **исключений** в любом месте.
 *
 * @api
 */
final class EventException extends LogicException
{
    private function __construct(
        public readonly ?bool $isHandled
    ) {
        parent::__construct('Event Internal Exception');
    }

    /**
     * Прервать обработку **события** или **исключения**, установить **событию** статус `обработано`.
     *
     * @api
     */
    public static function break(): never
    {
        throw new self(true);
    }

    /**
     * Прервать обработку **события** или **исключения**, установить **событию** статус `не обработано`.
     *
     * @api
     */
    public static function continue(): never
    {
        throw new self(false);
    }

    /**
     * Прервать обработку **события** или **исключения**, статус *событию** не менять.
     *
     * @api
     */
    public static function exit(): never
    {
        throw new self(null);
    }
}
