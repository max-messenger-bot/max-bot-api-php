<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

/**
 * Событие добавления пользователя в чат или канал, где бот является администратором с правом
 * {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class UserAddedToChatUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user: array,
     *     inviter_id?: int,
     *     is_channel: bool
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int Идентификатор чата или канала, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return int|null Идентификатор пользователя, который добавил нового пользователя в чат или канал.
     *     Может быть `null`, если пользователь присоединился по ссылке.
     */
    public function getInviterId(): ?int
    {
        return $this->data['inviter_id'] ?? null;
    }

    /**
     * @return list<non-empty-string> Имена обязательных свойств обновления.
     */
    public function getRequiredProperties(): array
    {
        return ['chat_id', 'user', 'is_channel'];
    }

    /**
     * @return User Пользователь, добавленный в чат или канал.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return bool Указывает, что пользователь добавлен в канал, а не в чат.
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
