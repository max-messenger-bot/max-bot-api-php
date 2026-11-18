<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot;

use Closure;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

use function explode;
use function str_starts_with;
use function substr;

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
 */
final class CommandHandler
{
    /**
     * Создаёт обработчик команд с заданными обработчиками и настройками разбора.
     *
     * @param non-empty-string|null $commandSeparator Разделитель имени команды и дополнительного текста.
     * @param bool $allowChannels Разрешить обработку команд из каналов.
     * @param array<non-empty-string, list<Closure(MessageCreatedEvent, string, ?string): (bool|void)>> $commandHandlers
     *     Обработчики отдельных команд.
     * @param list<Closure(MessageCreatedEvent, string, ?string): (bool|void)> $commandsHandlers Обработчики команд,
     *     не обработанных ранее.
     */
    public function __construct(
        public ?string $commandSeparator = null,
        public bool $allowChannels = false,
        private array $commandHandlers = [],
        private array $commandsHandlers = [],
    ) {}

    /**
     * @return bool Разрешена ли обработка команд из каналов.
     */
    public function getAllowChannels(): bool
    {
        return $this->allowChannels;
    }

    /**
     * @return non-empty-string|null Разделитель имени команды и дополнительного текста или `null`, если он не задан.
     */
    public function getCommandSeparator(): ?string
    {
        return $this->commandSeparator;
    }

    /**
     * Обрабатывает команду из сообщения.
     *
     * @param MessageCreatedEvent $event Событие создания сообщения.
     * @return bool `true`, если один из обработчиков обработал команду.
     */
    public function handle(MessageCreatedEvent $event): bool
    {
        if (!$this->allowChannels && $event->isChannel()) {
            return false;
        }

        $text = $event->getMessage()->getBody()->getText();

        if (
            ($this->commandHandlers === [] && $this->commandsHandlers === [])
            || !$text
            || !str_starts_with($text, '/')
        ) {
            return false;
        }

        if ($this->commandSeparator === null) {
            $command = substr($text, 1);
            $payload = null;
        } else {
            $payloadParts = explode($this->commandSeparator, substr($text, 1), 2);
            $command = $payloadParts[0];
            $payload = $payloadParts[1] ?? null;
        }

        $event->userData['__command'] = $command;
        $event->userData['__payload'] = $payload;

        foreach ($this->commandHandlers[$command] ?? [] as $handler) {
            if ($event->handle(static fn(MessageCreatedEvent $event): ?bool => $handler($event, $command, $payload))) {
                return true;
            }
        }

        foreach ($this->commandsHandlers as $handler) {
            if ($event->handle(static fn(MessageCreatedEvent $event): ?bool => $handler($event, $command, $payload))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Регистрирует обработчик команды с указанным именем.
     *
     * @param non-empty-string $name Имя команды без символа `/`.
     * @param Closure(MessageCreatedEvent $event, string $command, ?string $payload): (bool|void) $handler
     *     Обработчик команды с её именем и текстом после разделителя; без дополнительных данных payload равен `null`.
     * @return $this
     */
    public function onCommand(string $name, Closure $handler): static
    {
        $this->commandHandlers[$name][] = $handler;

        return $this;
    }

    /**
     * Регистрирует обработчик команд, которые не обработали именные обработчики.
     *
     * @param Closure(MessageCreatedEvent $event, string $command, ?string $payload): (bool|void) $handler
     *     Обработчик оставшихся команд с именем команды и текстом после разделителя либо `null`.
     * @return $this
     */
    public function onCommands(Closure $handler): static
    {
        $this->commandsHandlers[] = $handler;

        return $this;
    }

    /**
     * @param bool $allowChannels Разрешить обработку команд из каналов.
     * @return $this
     */
    public function setAllowChannels(bool $allowChannels): self
    {
        $this->allowChannels = $allowChannels;

        return $this;
    }

    /**
     * @param non-empty-string|null $commandSeparator Разделитель имени команды и дополнительного текста;
     *     `null` отключает разделение.
     * @return $this
     */
    public function setCommandSeparator(?string $commandSeparator): self
    {
        $this->commandSeparator = $commandSeparator;

        return $this;
    }
}
