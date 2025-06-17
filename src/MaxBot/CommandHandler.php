<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\EventHandlers;

use Closure;
use MaxMessenger\Bot\Events\MessageCreatedEvent;
use MaxMessenger\Bot\Models\Enums\ChatType;
use Throwable;

/**
 * @api
 */
final class CommandHandler
{
    public int $commandMaxLength = 64;
    public string|null $commandSplitChars = ' ';

    /**
     * @param array<non-empty-string, list<Closure(MessageCreatedEvent): void>> $commandHandlers
     * @param list<Closure(MessageCreatedEvent): void> $commandsHandlers
     */
    public function __construct(
        private array $commandHandlers = [],
        private array $commandsHandlers = [],
    ) {
    }

    public function handle(MessageCreatedEvent $event): void
    {
        $text = $this->getTextFromDialogEvent($event);

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (!$text || !$this->textIsCommand($text) || ($command = $this->extractCommandFromText($text)) === null) {
            $event->markAsUnhandled();

            return;
        }

        foreach ($this->commandHandlers[$command] ?? [] as $handler) {
            if ($event->handle($handler)) {
                return;
            }
        }

        foreach ($this->commandsHandlers as $handler) {
            if ($event->handle($handler)) {
                return;
            }
        }

        $event->markAsUnhandled();
    }

    /**
     * @param non-empty-string $name
     * @param Closure(MessageCreatedEvent $event): void $handler
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
     * @api
     */
    public function setCommandMaxLength(int $commandMaxLength): self
    {
        $this->commandMaxLength = $commandMaxLength;

        return $this;
    }

    /**
     *  Use this property to specify which characters are used as separators between commands and additional content.
     *
     *  **Warning:** Only ASCII characters are safe.
     *
     * @return $this
     * @api
     */
    public function setCommandSplitChars(string $commandSplitChars): self
    {
        $this->commandSplitChars = $commandSplitChars;

        return $this;
    }

    /**
     * @param non-falsy-string $text A character string longer than 2 bytes, starting with the character `/`.
     */
    private function extractCommandFromText(string $text): ?string
    {
        if ($this->commandSplitChars === null || $this->commandSplitChars === '') {
            return mb_strlen($text, 'UTF-8') <= ($this->commandMaxLength + 1)
                ? substr($text, 1)
                : null;
        }

        $command = mb_substr($text, 1, $this->commandMaxLength + 1, 'UTF-8');
        $len = strcspn($command, $this->commandSplitChars);
        $command = substr($command, 0, $len);

        return $command !== '' && mb_strlen($command, 'UTF-8') <= $this->commandMaxLength
            ? $command
            : null;
    }

    private function getTextFromDialogEvent(MessageCreatedEvent $event): ?string
    {
        $message = $event->getMessage();

        if ($message->getRecipient()->getChatType() !== ChatType::Dialog) {
            return null;
        }

        return $message->getBody()?->getText();
    }

    /**
     * @param non-falsy-string $text
     */
    private function textIsCommand(string $text): bool
    {
        try {
            return preg_match('~^/\p{L}~u', $text) === 1;
        } catch (Throwable) {
            return false;
        }
    }
}
