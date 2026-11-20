<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Событие добавления бота в чат или канал.
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class BotAddedToChatUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user?: array,
     *     is_channel: bool
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false|null $user = false;

    /**
     * @return int Идентификатор чата или канала, куда был добавлен бот.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return list<non-empty-string> Имена обязательных свойств обновления.
     */
    public function getRequiredProperties(): array
    {
        return ['chat_id', 'is_channel'];
    }

    /**
     * @return User|null Пользователь, добавивший бота в чат или канал, если он передан в обновлении.
     */
    public function getUser(): ?User
    {
        return $this->user === false
            ? $this->user = User::newFromNullableData($this->data['user'] ?? null)
            : $this->user;
    }

    /**
     * @return bool Указывает, что бот добавлен в канал, а не в чат.
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
