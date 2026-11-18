<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\MaxApiClient;

/**
 * Информация о боте, включая URL аватара и описание.
 *
 * Дополнительно содержит список команд, поддерживаемых ботом.
 * Возвращается только при вызове метода {@see MaxApiClient::getMyInfo()}
 *
 * @link https://dev.max.ru/docs-api/objects/BotInfo
 */
class BotInfo extends UserWithPhoto
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
     * @return non-empty-list<BotCommand>|null Команды, поддерживаемые ботом (maxItems: 32).
     */
    public function getCommands(): ?array
    {
        return $this->commands === false
            ? ($this->commands = BotCommand::newListFromNullableData($this->data['commands'] ?? null))
            : $this->commands;
    }
}
