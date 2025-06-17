<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\Message;
use MaxMessenger\Bot\Models\Responses\MessageEditedUpdate;

/**
 * @property-read MessageEditedUpdate $update
 */
final class MessageEditedEvent extends BaseEvent
{
    use MessageEventTrait;

    public function getMessage(): Message
    {
        return $this->update->getMessage();
    }
}
