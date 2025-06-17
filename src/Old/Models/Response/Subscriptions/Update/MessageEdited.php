<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Response\Shared\Message;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class MessageEdited extends Update
{
    private ?Message $message = null;

    /**
     * Отредактированное сообщение.
     */
    public function getMessage(): Message
    {
        /** @psalm-suppress MixedArgument */
        return $this->message ??= new Message($this->raw['message']);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): UpdateType
    {
        return UpdateType::MessageEdited;
    }
}
