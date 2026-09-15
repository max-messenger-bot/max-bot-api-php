<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Exception\Validation\MaxItemsException;

use function array_key_exists;
use function array_values;
use function count;

/**
 * Объект для обновления команд бота.
 */
final class BotCommandsPatch extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

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
     * @param BotCommand $command
     * @return $this
     */
    public function addCommand(BotCommand $command): self
    {
        if (!array_key_exists('commands', $this->data)) {
            $this->data['commands'] = [$command];

            return $this;
        }

        if (count($this->data['commands']) >= 32) {
            throw new MaxItemsException('commands', 33, 32);
        }

        $this->data['commands'][] = $command;

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
