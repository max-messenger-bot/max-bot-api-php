<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Events;

use MaxMessenger\Bot\Models\Responses\BotAddedUpdate;

/**
 * @property-read BotAddedUpdate $update
 * @api
 */
final class BotAddedEvent extends BaseEvent
{
}
