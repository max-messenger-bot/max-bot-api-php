<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Exceptions\MaxBot\Events\EventException;

/**
 * Вспомогательный класс для управления потоком обработки событий.
 *
 * Содержит статические методы для прерывания обработки событий и исключений
 * с возможностью управления статусом обработки (обработано/не обработано).
 *
 * Методы класса бросают {@see EventException} для немедленного завершения
 * цепочки обработчиков:
 * - {@see break()} — завершает обработку, помечая событие как обработанное
 * - {@see continue()} — завершает обработку, помечая событие как необработанное
 * - {@see exit()} — завершает обработку без изменения статуса
 */
final class Event
{
    /**
     * Прервать обработку **события** или **исключения**, установить **событию** статус `обработано`.
     */
    public static function break(): never
    {
        EventException::break();
    }

    /**
     * Прервать обработку **события** или **исключения**, установить **событию** статус `не обработано`.
     */
    public static function continue(): never
    {
        EventException::continue();
    }

    /**
     * Прервать обработку **события** или **исключения**, статус *событию** не менять.
     */
    public static function exit(): never
    {
        EventException::exit();
    }
}
