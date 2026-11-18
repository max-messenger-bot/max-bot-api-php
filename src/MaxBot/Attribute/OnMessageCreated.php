<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Attribute;

use Attribute;
use Closure;
use MaxMessenger\Bot\MaxBot;

/**
 * Регистрирует метод через {@see MaxBot::onMessageCreated()}.
 *
 * Ожидаемая сигнатура метода:
 * `callable(MessageCreatedEvent $event): (bool|void)`.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class OnMessageCreated implements HandlerAttributeInterface
{
    /**
     * Создаёт атрибут регистрации обработчика.
     *
     * @param bool $prepend Добавить обработчик в начало очереди.
     */
    public function __construct(
        public bool $prepend = false,
    ) {}

    /**
     * Регистрирует метод в обработчиках бота.
     *
     * @param MaxBot $bot Бот, в котором регистрируется обработчик.
     * @param Closure $handler Метод объекта, преобразованный в замыкание.
     */
    public function register(MaxBot $bot, Closure $handler): void
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $bot->onMessageCreated($handler, $this->prepend);
    }
}
