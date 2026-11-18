<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Attribute;

use Attribute;
use Closure;
use MaxMessenger\Bot\MaxBot;

/**
 * Регистрирует метод через {@see MaxBot::onCommand()}.
 *
 * Ожидаемая сигнатура метода:
 * `callable(MessageCreatedEvent $event, string $command, ?string $payload): (bool|void)`.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class OnCommand implements HandlerAttributeInterface
{
    /**
     * Создаёт атрибут регистрации обработчика.
     *
     * @param non-empty-string $name Имя команды.
     */
    public function __construct(
        public string $name,
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
        $bot->onCommand($this->name, $handler);
    }
}
