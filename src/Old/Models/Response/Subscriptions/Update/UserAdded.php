<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Response\Shared\User;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class UserAdded extends Update
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
     * Пользователь, который добавил пользователя в чат.
     * Может быть `null`, если пользователь присоединился к чату по ссылке.
     */
    public function getInviterId(): ?int
    {
        /** @var int|null */
        return $this->raw['inviter_id'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): UpdateType
    {
        return UpdateType::UserAdded;
    }

    /**
     * Пользователь, добавленный в чат.
     */
    public function getUser(): User
    {
        /** @psalm-suppress MixedArgument */
        return $this->user ??= new User($this->raw['user']);
    }
}
