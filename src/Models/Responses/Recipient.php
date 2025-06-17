<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\ChatType;

/**
 * Новый получатель сообщения.
 *
 * Может быть пользователем или чатом.
 */
class Recipient extends BaseResponseModel
{
    /**
     * @var array{
     *     chat_id: int,
     *     chat_type: string,
     *     user_id?: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int ID чата.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return ChatType|null Тип чата.
     */
    public function getChatType(): ?ChatType
    {
        return ChatType::tryFrom($this->data['chat_type']);
    }

    /**
     * @return string Тип чата.
     */
    public function getChatTypeRaw(): string
    {
        return $this->data['chat_type'];
    }

    /**
     * @return int|null ID пользователя, если сообщение было отправлено пользователю.
     */
    public function getUserId(): ?int
    {
        return $this->data['user_id'] ?? null;
    }
}
