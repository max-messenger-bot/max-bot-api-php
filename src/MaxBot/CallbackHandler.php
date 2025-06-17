<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot;

use Closure;
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

/**
 * Вспомогательный класс для обработки нажатий Callback-кнопок.
 *
 * По умолчанию действия с дополнительными данными не поддерживаются, но вы можете активировать эту возможность,
 * присвоив свойству `$callbackHandler->actionSeparator` разделитель действий и данных.
 *
 * Максимальная длина действия по умолчанию соответствует 64 символам в кодировке UTF-8.
 */
final class CallbackHandler
{
    /**
     * @param array<non-empty-string, list<Closure(MessageCallbackEvent): (bool|void)>> $actionHandlers
     * @param non-empty-string|null $actionSeparator
     * @param positive-int $actionMaxLength
     */
    public function __construct(
        private array $actionHandlers = [],
        public ?string $actionSeparator = null,
        public int $actionMaxLength = 64
    ) {
    }

    /**
     * @return positive-int
     */
    public function getActionMaxLength(): int
    {
        return $this->actionMaxLength;
    }

    /**
     * @return non-empty-string|null
     */
    public function getActionSeparator(): ?string
    {
        return $this->actionSeparator;
    }

    public function handle(MessageCallbackEvent $event): bool
    {
        $action = $event->getCallback()->getPayload();

        if (str_starts_with($action, '{')) {
            return false;
        }

        if ($this->actionSeparator === null) {
            $payload = null;
        } else {
            $payloadParts = explode($this->actionSeparator, $action, 2);
            $action = $payloadParts[0];
            $payload = $payloadParts[1] ?? null;
        }

        if (mb_strlen($action, 'UTF-8') > $this->actionMaxLength) {
            return false;
        }

        $event->userData['__action'] = $action;
        $event->userData['__payload'] = $payload;

        foreach ($this->actionHandlers[$action] ?? [] as $handler) {
            if ($event->handle($handler)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param non-empty-string $name
     * @param Closure(MessageCallbackEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onAction(string $name, Closure $handler): static
    {
        $this->actionHandlers[$name][] = $handler;

        return $this;
    }

    /**
     * @param positive-int $actionMaxLength
     * @return $this
     */
    public function setActionMaxLength(int $actionMaxLength): self
    {
        $this->actionMaxLength = $actionMaxLength;

        return $this;
    }

    /**
     * @param non-empty-string|null $actionSeparator
     * @return $this
     */
    public function setActionSeparator(?string $actionSeparator): self
    {
        $this->actionSeparator = $actionSeparator;

        return $this;
    }
}
