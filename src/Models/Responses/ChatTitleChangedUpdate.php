<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Бот получит это обновление, когда будет изменено название чата.
 */
class ChatTitleChangedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user: array,
     *     title: non-empty-string
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
     * @return non-empty-string Новое название (minLength: 1).
     */
    public function getTitle(): string
    {
        return $this->data['title'];
    }

    /**
     * @return User Пользователь, который изменил название.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }
}
