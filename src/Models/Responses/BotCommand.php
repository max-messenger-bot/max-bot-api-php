<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Команды, поддерживаемые ботом.
 */
class BotCommand extends BaseResponseModel
{
    /**
     * @var array{
     *     name: non-empty-string,
     *     description?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string|null Описание команды (minLength: 1, maxLength: 128).
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return non-empty-string Название команды (minLength: 1, maxLength: 64).
     */
    public function getName(): string
    {
        return $this->data['name'];
    }
}
