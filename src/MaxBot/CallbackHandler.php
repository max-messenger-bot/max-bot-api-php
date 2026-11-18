<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot;

use Closure;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

use function explode;
use function str_starts_with;

/**
 * Вспомогательный класс для обработки нажатий Callback-кнопок.
 *
 * По умолчанию действия с дополнительными данными не поддерживаются, но вы можете активировать эту возможность,
 * присвоив свойству `$callbackHandler->actionSeparator` разделитель действий и данных.
 */
final class CallbackHandler
{
    /**
     * Создаёт обработчик нажатий Callback-кнопок с заданными действиями и настройками разбора.
     *
     * @param non-empty-string|null $actionSeparator Разделитель имени действия и дополнительного текста.
     * @param array<non-empty-string, list<Closure(MessageCallbackEvent, string, ?string): (bool|void)>> $actionHandlers
     *     Обработчики отдельных действий.
     */
    public function __construct(
        public ?string $actionSeparator = null,
        private array $actionHandlers = [],
    ) {}

    /**
     * @return non-empty-string|null Разделитель имени действия и дополнительного текста или `null`, если он не задан.
     */
    public function getActionSeparator(): ?string
    {
        return $this->actionSeparator;
    }

    /**
     * Обрабатывает нажатие Callback-кнопки с текстовым действием.
     *
     * @param MessageCallbackEvent $event Событие нажатия Callback-кнопки.
     * @return bool `true`, если обработчик обработал действие.
     */
    public function handle(MessageCallbackEvent $event): bool
    {
        $action = $event->getCallback()->getPayload();

        if ($this->actionHandlers === [] || str_starts_with($action, '{')) {
            return false;
        }

        if ($this->actionSeparator === null) {
            $payload = null;
        } else {
            $payloadParts = explode($this->actionSeparator, $action, 2);
            $action = $payloadParts[0];
            $payload = $payloadParts[1] ?? null;
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
     * @param Closure(MessageCallbackEvent $event, string $action, ?string $payload): (bool|void) $handler
     *     Обработчик действия с его именем и текстом после разделителя; без дополнительных данных payload равен `null`.
     * @return $this
     */
    public function onAction(string $name, Closure $handler): static
    {
        $this->actionHandlers[$name][] = $handler;

        return $this;
    }

    /**
     * @param non-empty-string|null $actionSeparator Разделитель имени действия и дополнительного текста;
     *     `null` отключает разделение.
     * @return $this
     */
    public function setActionSeparator(?string $actionSeparator): self
    {
        $this->actionSeparator = $actionSeparator;

        return $this;
    }
}
