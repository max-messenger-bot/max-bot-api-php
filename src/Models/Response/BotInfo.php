<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Bot information.
 *
 * @api
 */
readonly class BotInfo extends UserWithPhoto
{
    /**
     * @var array{
     *     commands?: list<array>|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return list<BotCommand>|null Commands supported by bot (maxItems: 32).
     * @api
     */
    public function getCommands(): ?array
    {
        return BotCommand::newListFromNullableData($this->data['commands'] ?? null);
    }
}
