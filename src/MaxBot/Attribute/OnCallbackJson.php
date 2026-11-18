<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Attribute;

use Attribute;
use Closure;
use MaxMessenger\Bot\MaxBot;

/**
 * Регистрирует метод через {@see MaxBot::onCallbackJson()}.
 *
 * Ожидаемая сигнатура метода:
 * `callable(MessageCallbackEvent $event, string $action, array $payload): (bool|void)`.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class OnCallbackJson implements HandlerAttributeInterface
{
    /**
     * Создаёт атрибут регистрации обработчика.
     *
     * @param non-empty-string $name Имя действия.
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
        $bot->onCallbackJson($this->name, $handler);
    }
}
