<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Events;

use MaxMessenger\Bot\Models\Responses\MessageEditedUpdate;

/**
 * @property-read MessageEditedUpdate $update
 * @api
 */
final class MessageEditedEvent extends BaseEvent
{
}
