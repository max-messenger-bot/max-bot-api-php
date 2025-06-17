<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Response\Shared\Callback;
use MaxMessenger\Api\Old\Models\Response\Shared\Message;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class MessageCallback extends Update
{
    private ?Callback $callback = null;
    private Message|false|null $message = null;

    public function getCallback(): Callback
    {
        /** @psalm-suppress MixedArgument */
        return $this->callback ??= new Callback($this->raw['callback']);
    }

    /**
     * Изначальное сообщение, содержащее встроенную клавиатуру.
     * Может быть `null`, если оно было удалено к моменту, когда бот получил это обновление.
     */
    public function getMessage(): ?Message
    {
        if (isset($this->message)) {
            return $this->message ?: null;
        }

        /** @var array|null $rawMessage */
        $rawMessage = $this->raw['message'] ?? null;

        return $this->message = $rawMessage !== null
            ? new Message($rawMessage)
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): UpdateType
    {
        return UpdateType::MessageCallback;
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
