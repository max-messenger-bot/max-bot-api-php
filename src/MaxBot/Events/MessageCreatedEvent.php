<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\Message;
use MaxMessenger\Bot\Models\Responses\MessageCreatedUpdate;

/**
 * @property-read MessageCreatedUpdate $update
 * @api
 */
final class MessageCreatedEvent extends BaseEvent
{
    private Message|null $message = null;

    public function getMessage(): Message
    {
        return $this->message ??= $this->update->getMessage();
    }
}
