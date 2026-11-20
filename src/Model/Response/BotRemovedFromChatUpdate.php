<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Событие удаления бота из чата или канала.
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class BotRemovedFromChatUpdate extends Update
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
     * @return int Идентификатор чата или канала, откуда был удалён бот.
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
     * @return User|null Пользователь, удаливший бота из чата или канала, если он передан в обновлении.
     */
    public function getUser(): ?User
    {
        return $this->user === false
            ? $this->user = User::newFromNullableData($this->data['user'] ?? null)
            : $this->user;
    }

    /**
     * @return bool Указывает, что бот удалён из канала, а не из чата.
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
