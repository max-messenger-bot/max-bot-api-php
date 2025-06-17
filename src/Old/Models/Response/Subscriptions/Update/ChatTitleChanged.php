<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Response\Shared\User;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class ChatTitleChanged extends Update
{
    private ?User $user = null;

    /**
     * ID чата, где произошло событие.
     */
    public function getChatId(): ?string
    {
        /** @var string|null */
        return $this->raw['chat_id'] ?? null;
    }

    /**
     * Новое название.
     */
    public function getTitle(): ?string
    {
        /** @var string|null */
        return $this->raw['title'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): UpdateType
    {
        return UpdateType::ChatTitleChanged;
    }

    /**
     * Пользователь, который изменил название.
     */
    public function getUser(): User
    {
        /** @psalm-suppress MixedArgument */
        return $this->user ??= new User($this->raw['user']);
    }
}
