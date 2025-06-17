<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\DialogMutedUpdate;

/**
 * @property-read DialogMutedUpdate $update
 * @api
 */
final class DialogMutedEvent extends BaseEvent
{
}
