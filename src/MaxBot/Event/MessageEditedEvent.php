<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageEditedUpdate;

/**
 * @property-read MessageEditedUpdate $update
 */
final class MessageEditedEvent extends BaseEvent
{
    use MessageEventTrait;

    public function getMessage(): ?Message
    {
        return $this->update->getMessage();
    }
}
