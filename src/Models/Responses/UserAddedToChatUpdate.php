<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Вы получите это обновление, когда пользователь будет добавлен в чат, где бот является администратором.
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
     * @return int ID чата, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return int|null Пользователь, который добавил пользователя в чат.
     *     Может быть `null`, если пользователь присоединился к чату по ссылке.
     */
    public function getInviterId(): ?int
    {
        return $this->data['inviter_id'] ?? null;
    }

    /**
     * @return User Пользователь, добавленный в чат.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return bool Указывает, был ли пользователь добавлен в канал или нет.
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
