<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Events;

use MaxMessenger\Bot\Models\Responses\BotRemovedUpdate;

/**
 * @property-read BotRemovedUpdate $update
 * @api
 */
final class BotRemovedEvent extends BaseEvent
{
}
