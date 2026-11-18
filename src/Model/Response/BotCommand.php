<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Команды, поддерживаемые ботом.
 *
 * @link https://dev.max.ru/docs-api/objects/BotInfo/
 * @link https://dev.max.ru/docs-api/methods/GET/me/
 * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands/
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
     * @return non-empty-string|null Описание команды (maxLength: 128).
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return non-empty-string Название команды (maxLength: 64).
     */
    public function getName(): string
    {
        return $this->data['name'];
    }
}
