<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вы получите это событие, как только бот будет удалён из чата.
 */
class BotRemovedFromChatUpdate extends Update
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
     * @return int ID чата, откуда был удалён бот.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return User Пользователь, удаливший бота из чата.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
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
