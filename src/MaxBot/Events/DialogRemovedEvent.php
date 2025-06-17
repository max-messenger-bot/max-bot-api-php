<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\DialogRemovedUpdate;

/**
 * @property-read DialogRemovedUpdate $update
 * @api
 */
final class DialogRemovedEvent extends BaseEvent
{
}
