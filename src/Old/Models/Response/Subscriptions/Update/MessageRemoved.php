<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class MessageRemoved extends Update
{
    /**
     * ID чата, где сообщение было удалено.
     */
    public function getChatId(): ?string
    {
        /** @var string|null */
        return $this->raw['chat_id'] ?? null;
    }

    /**
     * ID удаленного сообщения.
     */
    public function getMessageId(): ?string
    {
        /** @var string|null */
        return $this->raw['message_id'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): UpdateType
    {
        return UpdateType::MessageRemoved;
    }

    /**
     * Пользователь, удаливший сообщение.
     */
    public function getUserId(): ?string
    {
        /** @var string|null */
        return $this->raw['user_id'] ?? null;
    }
}
