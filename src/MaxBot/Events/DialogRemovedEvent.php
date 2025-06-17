<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Events;

use MaxMessenger\Bot\Models\Responses\DialogRemovedUpdate;

/**
 * @property-read DialogRemovedUpdate $update
 * @api
 */
final class DialogRemovedEvent extends BaseEvent
{
}
