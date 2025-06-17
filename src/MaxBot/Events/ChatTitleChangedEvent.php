<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Events;

use MaxMessenger\Bot\Models\Responses\ChatTitleChangedUpdate;

/**
 * @property-read ChatTitleChangedUpdate $update
 * @api
 */
final class ChatTitleChangedEvent extends BaseEvent
{
}
