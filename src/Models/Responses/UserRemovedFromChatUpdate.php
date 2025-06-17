<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Вы получите это обновление, когда пользователь будет удалён из чата, где бот является администратором.
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
     * @return int|null Администратор, который удалил пользователя из чата.
     *     Может быть `null`, если пользователь покинул чат сам.
     */
    public function getAdminId(): ?int
    {
        return $this->data['admin_id'] ?? null;
    }

    /**
     * @return int ID чата, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return User Пользователь, удалённый из чата.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return bool Указывает, был ли пользователь удалён из канала или нет.
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
