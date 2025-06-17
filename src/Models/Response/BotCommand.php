<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Bot command.
 *
 * @api
 */
readonly class BotCommand extends BaseResponseModel
{
    /**
     * @var array{
     *     name: non-empty-string,
     *     description?: non-empty-string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return non-empty-string|null Optional command description (minLength: 1, maxLength: 128).
     * @api
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return non-empty-string Command name (minLength: 1, maxLength: 64).
     * @api
     */
    public function getName(): string
    {
        return $this->data['name'];
    }
}
