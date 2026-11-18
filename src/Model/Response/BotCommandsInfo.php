<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Информация о командах бота.
 *
 * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands/
 */
class BotCommandsInfo extends BaseResponseModel
{
    /**
     * @var array{
     *     commands?: non-empty-list<array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var non-empty-list<BotCommand>|false|null
     */
    private array|false|null $commands = false;

    /**
     * @return non-empty-list<BotCommand>|null Команды, которые поддерживает бот (maxItems: 32).
     */
    public function getCommands(): ?array
    {
        return $this->commands === false
            ? ($this->commands = BotCommand::newListFromNullableData($this->data['commands'] ?? null))
            : $this->commands;
    }
}
