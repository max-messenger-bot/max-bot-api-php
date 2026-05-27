<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxApiLogicException;
use MaxMessenger\Bot\MaxBot\Event\Event;

/**
 * Служебное исключение для завершения обработки.
 *
 * Используется для явного управления потоком обработки событий изнутри обработчика. Позволяет завершить
 * обработку **событий** или **исключений** с определённым статусом без необходимости возвращать значение.
 *
 * Бросается при вызове статических методов {@see Event::break()}, {@see Event::continue()} или {@see Event::exit()}.
 *
 * **Варианты использования:**
 *
 * - {@see Event::break()} — завершает обработку **события** или **исключения**, помечая событие как обработанное
 * - {@see Event::continue()} — завершает обработку **события** или **исключения**, помечая событие как необработанное
 * - {@see Event::exit()}` — завершает обработку **события** или **исключения** без изменения статуса события
 */
final class EventException extends MaxApiLogicException
{
    /**
     * Создаёт исключение с указанием статуса обработки события.
     *
     * @param bool|null $isHandled Статус обработки события. `true` — событие обработано, `false` — не обработано,
     *     `null` — не менять статус.
     */
    public function __construct(
        public readonly ?bool $isHandled = null
    ) {
        parent::__construct('Internal Event Exception');
    }
}
