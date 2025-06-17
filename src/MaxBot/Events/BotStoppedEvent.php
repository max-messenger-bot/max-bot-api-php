<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\BotStoppedUpdate;

/**
 * @property-read BotStoppedUpdate $update
 * @api
 */
final class BotStoppedEvent extends BaseEvent
{
}
