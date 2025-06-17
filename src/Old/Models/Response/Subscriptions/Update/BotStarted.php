<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Response\Shared\User;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class BotStarted extends Update
{
    private ?User $user = null;

    /**
     * ID диалога, где произошло событие.
     */
    public function getChatId(): ?string
    {
        /** @var string|null */
        return $this->raw['chat_id'] ?? null;
    }

    /**
     * Дополнительные данные из дип-линков, переданные при запуске бота. До `512` символов.
     */
    public function getPayload(): ?string
    {
        /** @var string|null */
        return $this->raw['payload'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): UpdateType
    {
        return UpdateType::BotStarted;
    }

    /**
     * Пользователь, который нажал кнопку `Start`.
     */
    public function getUser(): User
    {
        /** @psalm-suppress MixedArgument */
        return $this->user ??= new User($this->raw['user']);
    }

    /**
     * Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        /** @var string|null */
        return $this->raw['user_locale'] ?? null;
    }
}
