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
 *
 * По умолчанию команды с дополнительными данными не поддерживаются, но вы можете активировать эту возможность,
 * присвоив свойству `$commandHandler->commandSeparator` разделитель команд и данных.
 */
final class CommandHandler
{
    public int $commandMaxLength = 64;
    public ?string $commandSeparator = null;

    /**
     * @param array<non-empty-string, list<Closure(MessageCreatedEvent): (bool|void)>> $commandHandlers
     * @param list<Closure(MessageCreatedEvent): (bool|void)> $commandsHandlers
     */
    public function __construct(
        private array $commandHandlers = [],
        private array $commandsHandlers = [],
    ) {
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
            $command = mb_strlen($text) <= $this->commandMaxLength + 1
                ? substr($text, 1)
                : null;
        } else {
            $command = mb_substr($text, 1, $this->commandMaxLength + mb_strlen($this->commandSeparator), 'UTF-8');
            $pos = strpos($command, $this->commandSeparator);
            $command = $pos !== false
                ? substr($command, 0, $pos)
                : null;
        }

        $event->userData['__command'] = $command;

        if ($command !== null) {
            foreach ($this->commandHandlers[$command] ?? [] as $handler) {
                if ($event->handle($handler)) {
                    return true;
                }
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
     * @param Closure(MessageCreatedEvent $event): (bool|null) $handler
     * @return $this
     */
    public function onCommand(string $name, Closure $handler): static
    {
        $this->commandHandlers[$name][] = $handler;

        return $this;
    }

    /**
     * @param Closure(MessageCreatedEvent $event): void $handler
     * @return $this
     */
    public function onCommands(Closure $handler): static
    {
        $this->commandsHandlers[] = $handler;

        return $this;
    }

    /**
     * @return $this
     */
    public function setCommandMaxLength(int $commandMaxLength): self
    {
        $this->commandMaxLength = $commandMaxLength;

        return $this;
    }

    /**
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
