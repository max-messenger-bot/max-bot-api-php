<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Вы получите этот `update`, как только сообщение будет удалено.
 */
class MessageRemovedUpdate extends Update
{
    /**
     * @var array{
     *     message_id: non-empty-string,
     *     chat_id: int,
     *     user_id: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int ID чата, где сообщение было удалено.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return non-empty-string ID удаленного сообщения (minLength: 1).
     */
    public function getMessageId(): string
    {
        return $this->data['message_id'];
    }

    /**
     * @return int Пользователь, удаливший сообщение.
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }
}
