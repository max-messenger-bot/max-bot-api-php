<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\ChatType;

/**
 * Получатель сообщения.
 *
 * Может быть пользователем, чатом или каналом.
 */
class Recipient extends BaseResponseModel
{
    /**
     * @var array{
     *     chat_id: int,
     *     chat_type: string,
     *     user_id?: int,
     *     post_id?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int ID чата или канала.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return ChatType|null Тип чата. Для комментариев определяет, от чьего имени оставлен комментарий:
     *     {@see ChatType::Channel} — от имени канала, {@see ChatType::Chat} — от имени пользователя или бота.
     */
    public function getChatType(): ?ChatType
    {
        return ChatType::tryFrom($this->data['chat_type']);
    }

    /**
     * @return string Тип чата. Для комментариев определяет, от чьего имени оставлен комментарий.
     */
    public function getChatTypeRaw(): string
    {
        return $this->data['chat_type'];
    }

    /**
     * @return non-empty-string|null Идентификатор поста в канале, к которому оставлен комментарий.
     *     Возвращается только для комментариев.
     */
    public function getPostId(): ?string
    {
        return $this->data['post_id'] ?? null;
    }

    /**
     * @return int|null ID получателя сообщения в диалоге (пользователя или бота).
     *     Если сообщение отправлено в групповой чат или канал, возвращается `null`.
     */
    public function getUserId(): ?int
    {
        return $this->data['user_id'] ?? null;
    }
}
