<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot;

use Closure;
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;
use MaxMessenger\Bot\Models\Enums\ChatType;

/**
 * Вспомогательный класс для обработки команд.
 *
 * Командой считается всё, что начинается с символа `/`.
 *
 * Вы можете установить обработчик на конкретную команду или на любую не обработанную ранее команду.
 *
 * Если обработчик команд обнаружил в сообщении команду, она будет записана в `$event->userData['__command']`.
 * Дополнительный текст команды (payload) записывается в `$event->userData['__payload']`.
 *
 * По умолчанию команды с дополнительными данными не поддерживаются, но вы можете активировать эту возможность,
 * присвоив свойству `$commandHandler->commandSeparator` разделитель команд и данных.
 *
 * Максимальная длина команды по умолчанию соответствует 64 символам в кодировке UTF-8 (как в схеме API Max).
 */
final class CommandHandler
{
    /**
     * @param array<non-empty-string, list<Closure(MessageCreatedEvent): (bool|void)>> $commandHandlers
     * @param list<Closure(MessageCreatedEvent): (bool|void)> $commandsHandlers
     * @param non-empty-string|null $commandSeparator
     * @param positive-int $commandMaxLength
     */
    public function __construct(
        private array $commandHandlers = [],
        private array $commandsHandlers = [],
        public ?string $commandSeparator = null,
        public int $commandMaxLength = 64
    ) {
    }

    /**
     * @return positive-int
     */
    public function getCommandMaxLength(): int
    {
        return $this->commandMaxLength;
    }

    /**
     * @return non-empty-string|null
     */
    public function getCommandSeparator(): ?string
    {
        return $this->commandSeparator;
    }

    public function handle(MessageCreatedEvent $event): bool
    {
        $text = $this->getTextFromDialogEvent($event);

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (!$text || !str_starts_with($text, '/')) {
            return false;
        }
        /** @psalm-var non-falsy-string $text Psalm bug */

        if ($this->commandSeparator === null) {
            $command = mb_substr($text, 1, $this->commandMaxLength + 1, 'UTF-8');
            $payload = null;
        } else {
            $payloadParts = explode($this->commandSeparator, substr($text, 1), 2);
            $command = $payloadParts[0];
            $payload = $payloadParts[1] ?? null;
        }

        if (mb_strlen($command, 'UTF-8') > $this->commandMaxLength) {
            return false;
        }

        $event->userData['__command'] = $command;
        $event->userData['__payload'] = $payload;

        foreach ($this->commandHandlers[$command] ?? [] as $handler) {
            if ($event->handle($handler)) {
                return true;
            }
        }

        foreach ($this->commandsHandlers as $handler) {
            if ($event->handle($handler)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param non-empty-string $name
     * @param Closure(MessageCreatedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onCommand(string $name, Closure $handler): static
    {
        $this->commandHandlers[$name][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageCreatedEvent $event): (bool|void) $handler
     * @return $this
     */
    public function onCommands(Closure $handler): static
    {
        $this->commandsHandlers[] = $handler;

        return $this;
    }

    /**
     * @param positive-int $commandMaxLength
     * @return $this
     */
    public function setCommandMaxLength(int $commandMaxLength): self
    {
        $this->commandMaxLength = $commandMaxLength;

        return $this;
    }

    /**
     * @param non-empty-string|null $commandSeparator
     * @return $this
     */
    public function setCommandSeparator(?string $commandSeparator): self
    {
        $this->commandSeparator = $commandSeparator;

        return $this;
    }

    private function getTextFromDialogEvent(MessageCreatedEvent $event): ?string
    {
        $message = $event->getMessage();

        if ($message->getRecipient()->getChatType() !== ChatType::Dialog) {
            return null;
        }

        return $message->getBody()->getText();
    }
}
