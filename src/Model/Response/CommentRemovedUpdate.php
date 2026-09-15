<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вы получите это событие, как только комментарий будет удалён.
 */
class CommentRemovedUpdate extends Update
{
    /**
     * @var array{
     *     message_id: non-empty-string,
     *     chat_id: int,
     *     user_id: int,
     *     post_id: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int ID чата, где комментарий был удалён.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return non-empty-string ID удалённого комментария.
     */
    public function getMessageId(): string
    {
        return $this->data['message_id'];
    }

    /**
     * @return non-empty-string Идентификатор поста в канале.
     */
    public function getPostId(): string
    {
        return $this->data['post_id'];
    }

    /**
     * @return int Пользователь, удаливший комментарий.
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }
}
