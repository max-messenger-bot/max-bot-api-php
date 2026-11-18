<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

/**
 * Событие удаления пользователя из чата или канала, где бот является администратором с правом
 * {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class UserRemovedFromChatUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user: array,
     *     admin_id?: int,
     *     is_channel: bool
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int|null Идентификатор администратора, который удалил пользователя из чата или канала.
     *     Может быть `null`, если пользователь покинул чат или канал сам.
     */
    public function getAdminId(): ?int
    {
        return $this->data['admin_id'] ?? null;
    }

    /**
     * @return int Идентификатор чата или канала, где произошло событие.
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
        return ['chat_id', 'user', 'is_channel'];
    }

    /**
     * @return User Пользователь, удалённый из чата или канала.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return bool Указывает, что пользователь удалён из канала, а не из чата.
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
