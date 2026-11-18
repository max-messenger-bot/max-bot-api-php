<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot;

use MaxMessenger\Bot\MaxBot;

/**
 * Тип списка обработчиков события.
 *
 * Используется в свойствах события `currentHandlerListType` и `handledIn`.
 */
enum HandlerListType
{
    /**
     * Произвольный список, переданный в {@see MaxBot::handleEventUseHandlerList()}.
     */
    case Custom;
    /**
     * Обработчики конкретного класса события.
     */
    case Event;
    /**
     * Общие обработчики ещё не обработанных событий, зарегистрированные через {@see MaxBot::onFallback()}.
     */
    case Fallback;
    /**
     * Завершающие обработчики, зарегистрированные через {@see MaxBot::onFinal()}.
     */
    case Final;
    /**
     * Предварительные обработчики, зарегистрированные через {@see MaxBot::onPrepare()}.
     */
    case Prepare;
    /**
     * Обработчики типа обновления, зарегистрированные через {@see MaxBot::on()}.
     */
    case Typed;
}
