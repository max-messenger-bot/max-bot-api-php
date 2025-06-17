<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\BotStartedUpdate;

/**
 * @property-read BotStartedUpdate $update
 * @api
 */
final class BotStartedEvent extends BaseEvent
{
}
