<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Вы получите это событие, как только бот будет добавлен в чат или канал.
 */
class BotAddedToChatUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user: array,
     *     is_channel: bool
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int ID чата, куда был добавлен бот.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return User Пользователь, добавивший бота в чат.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
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
