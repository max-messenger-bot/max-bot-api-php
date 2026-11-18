<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Attribute;

use Closure;
use MaxMessenger\Bot\MaxBot;

/**
 * Атрибут регистрации метода объекта в обработчиках бота.
 */
interface HandlerAttributeInterface
{
    /**
     * Регистрирует метод в соответствующем списке обработчиков.
     *
     * @param MaxBot $bot Бот, в котором регистрируется обработчик.
     * @param Closure $handler Метод объекта, преобразованный в замыкание.
     */
    public function register(MaxBot $bot, Closure $handler): void;
}
