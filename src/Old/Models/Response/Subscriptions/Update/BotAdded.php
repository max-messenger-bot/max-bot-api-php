<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Response\Shared\User;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class BotAdded extends Update
{
    private ?User $user = null;

    /**
     * ID чата, куда был добавлен бот.
     */
    public function getChatId(): ?string
    {
        /** @var string|null */
        return $this->raw['chat_id'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): UpdateType
    {
        return UpdateType::BotAdded;
    }

    /**
     * Пользователь, добавивший бота в чат.
     */
    public function getUser(): User
    {
        /** @psalm-suppress MixedArgument */
        return $this->user ??= new User($this->raw['user']);
    }
}
