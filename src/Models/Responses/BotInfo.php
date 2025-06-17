<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\MaxApiClient;

/**
 * Объект включает общую информацию о боте, URL аватара и описание.
 *
 * Дополнительно содержит список команд, поддерживаемых ботом.
 * Возвращается только при вызове метода {@see MaxApiClient::getMyInfo()}.
 *
 * @link https://dev.max.ru/docs-api/objects/BotInfo
 * @api
 */
class BotInfo extends UserWithPhoto
{
    /**
     * @var array{
     *     commands?: list<array>|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<BotCommand>|false|null
     */
    private array|false|null $commands = false;

    /**
     * @return list<BotCommand>|null Команды, поддерживаемые ботом (maxItems: 32).
     * @api
     */
    public function getCommands(): ?array
    {
        return $this->commands === false
            ? ($this->commands = BotCommand::newListFromNullableData($this->data['commands'] ?? null))
            : $this->commands;
    }
}
