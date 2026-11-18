<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Attribute;

use Attribute;
use Closure;
use MaxMessenger\Bot\MaxBot;

/**
 * Регистрирует метод через {@see MaxBot::onUnknown()}.
 *
 * Ожидаемая сигнатура метода:
 * `callable(UnknownEvent $event): (bool|void)`.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class OnUnknown implements HandlerAttributeInterface
{
    /**
     * Регистрирует метод в обработчиках бота.
     *
     * @param MaxBot $bot Бот, в котором регистрируется обработчик.
     * @param Closure $handler Метод объекта, преобразованный в замыкание.
     */
    public function register(MaxBot $bot, Closure $handler): void
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $bot->onUnknown($handler);
    }
}
