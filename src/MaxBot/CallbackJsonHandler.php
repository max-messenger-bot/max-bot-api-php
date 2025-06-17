<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot;

use Closure;
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

use function is_array;
use function is_string;

/**
 * Вспомогательный класс для обработки нажатий Callback-кнопок с JSON-payload.
 *
 * Максимальная длина действия по умолчанию соответствует 64 символам в кодировке UTF-8.
 */
final class CallbackJsonHandler
{
    /**
     * @var positive-int
     */
    public int $actionMaxLength = 64;

    /**
     * @param non-empty-string $actionKey
     * @param array<non-empty-string, list<Closure(MessageCallbackEvent): (bool|void)>> $actionHandlers
     */
    public function __construct(
        public readonly string $actionKey,
        private array $actionHandlers = [],
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getActionKey(): string
    {
        return $this->actionKey;
    }

    /**
     * @return positive-int
     */
    public function getActionMaxLength(): int
    {
        return $this->actionMaxLength;
    }

    public function handle(MessageCallbackEvent $event): bool
    {
        $payload = $event->getCallback()->getPayload();

        /** @psalm-suppress MixedAssignment */
        $payload = str_starts_with($payload, '{')
            ? json_decode($payload, true)
            : null;
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (!is_array($payload) || empty($action = $payload[$this->actionKey] ?? null)) {
            return false;
        }

        if (!is_string($action) || mb_strlen($action, 'UTF-8') > $this->actionMaxLength) {
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
}
