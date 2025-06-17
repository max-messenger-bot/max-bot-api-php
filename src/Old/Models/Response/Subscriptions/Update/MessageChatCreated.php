<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Response\Shared\Chat;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class MessageChatCreated extends Update
{
    private ?Chat $chat = null;

    /**
     * Созданный чат.
     */
    public function getChat(): Chat
    {
        /** @psalm-suppress MixedArgument */
        return $this->chat ??= new Chat($this->raw['chat']);
    }

    /**
     * ID сообщения, где была нажата кнопка.
     */
    public function getMessageId(): ?string
    {
        /** @var string|null */
        return $this->raw['message_id'] ?? null;
    }

    /**
     * Полезная нагрузка от кнопки чата.
     */
    public function getStartPayload(): ?string
    {
        /** @var string|null */
        return $this->raw['start_payload'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): UpdateType
    {
        return UpdateType::MessageChatCreated;
    }
}
