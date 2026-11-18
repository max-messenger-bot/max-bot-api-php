<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use function array_key_exists;
use function array_values;

/**
 * Объект для обновления команд бота.
 *
 * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands/
 */
final class BotCommandsPatch extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

    /**
     * @var array{
     *     commands: list<BotCommand>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param BotCommand[]|null $commands Список команд и их описаний, отображаемых пользователю
     *     в подсказках при вводе `/` (maxItems: 32). Чтобы удалить все команды, передайте пустой список.
     */
    public function __construct(?array $commands = null)
    {
        $this->required = ['commands'];

        if ($commands !== null) {
            $this->setCommands($commands);
        }
    }

    /**
     * Добавляет команду в список команд бота.
     *
     * @param BotCommand $command Добавляемая команда.
     * @return $this
     */
    public function addCommand(BotCommand $command): self
    {
        if (!array_key_exists('commands', $this->data)) {
            $this->data['commands'] = [$command];

            return $this;
        }

        $commands = [...$this->data['commands'], $command];

        self::validateArray('command', $commands, maxItems: 32);

        $this->data['commands'] = $commands;

        return $this;
    }

    /**
     * @return list<BotCommand>
     */
    public function getCommands(): array
    {
        return $this->data['commands'];
    }

    public function issetCommands(): bool
    {
        return array_key_exists('commands', $this->data);
    }

    /**
     * @param BotCommand[] $commands Список команд и их описаний, отображаемых пользователю
     *     в подсказках при вводе `/` (maxItems: 32). Чтобы удалить все команды, передайте пустой список.
     */
    public static function make(array $commands): self
    {
        return new self($commands);
    }

    /**
     * @param BotCommand[]|null $commands Список команд и их описаний, отображаемых пользователю
     *     в подсказках при вводе `/` (maxItems: 32). Чтобы удалить все команды, передайте пустой список.
     */
    public static function new(?array $commands = null): self
    {
        return new self($commands);
    }

    /**
     * @param BotCommand[] $commands Список команд и их описаний, отображаемых пользователю
     *     в подсказках при вводе `/` (maxItems: 32). Чтобы удалить все команды, передайте пустой список.
     * @return $this
     */
    public function setCommands(array $commands): self
    {
        self::validateArray('commands', $commands, maxItems: 32);

        $this->data['commands'] = array_values($commands);

        return $this;
    }
}
