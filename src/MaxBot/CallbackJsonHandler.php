<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot;

use Closure;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

use function is_array;
use function is_string;
use function json_decode;
use function str_starts_with;

/**
 * Вспомогательный класс для обработки нажатий Callback-кнопок с JSON-payload.
 */
final class CallbackJsonHandler
{
    /**
     * Создаёт обработчик нажатий Callback-кнопок с JSON-payload.
     *
     * @param non-empty-string $actionKey Ключ JSON-поля с именем действия.
     * @param array<non-empty-string, list<Closure(MessageCallbackEvent, string, array): (bool|void)>>
     *     $actionHandlers Обработчики отдельных действий.
     */
    public function __construct(
        public string $actionKey = 'action',
        private array $actionHandlers = [],
    ) {}

    /**
     * @return non-empty-string Ключ JSON-поля с именем действия.
     */
    public function getActionKey(): string
    {
        return $this->actionKey;
    }

    /**
     * Обрабатывает нажатие Callback-кнопки с JSON-payload.
     *
     * @param MessageCallbackEvent $event Событие нажатия Callback-кнопки.
     * @return bool `true`, если обработчик обработал действие.
     */
    public function handle(MessageCallbackEvent $event): bool
    {
        $payload = $event->getCallback()->getPayload();

        if ($this->actionHandlers === [] || !str_starts_with($payload, '{')) {
            return false;
        }

        /** @psalm-suppress MixedAssignment */
        $payload = json_decode($payload, true);
        if (!is_array($payload)) {
            return false;
        }

        /** @psalm-suppress MixedAssignment */
        $action = $payload[$this->actionKey] ?? null;
        if (!is_string($action) || $action === '') {
            return false;
        }

        $event->userData['__action'] = $action;
        $event->userData['__payload'] = $payload;

        foreach ($this->actionHandlers[$action] ?? [] as $handler) {
            if ($event->handle(static fn(MessageCallbackEvent $event): ?bool => $handler($event, $action, $payload))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Регистрирует обработчик действия с указанным именем.
     *
     * @param non-empty-string $name Имя действия.
     * @param Closure(MessageCallbackEvent $event, string $action, array $payload): (bool|void) $handler
     *     Обработчик действия с его именем и всем декодированным JSON-payload, включая поле действия.
     * @return $this
     */
    public function onAction(string $name, Closure $handler): static
    {
        $this->actionHandlers[$name][] = $handler;

        return $this;
    }

    /**
     * @param non-empty-string $actionKey Ключ JSON-поля с именем действия.
     * @return $this
     */
    public function setActionKey(string $actionKey): self
    {
        $this->actionKey = $actionKey;

        return $this;
    }
}
