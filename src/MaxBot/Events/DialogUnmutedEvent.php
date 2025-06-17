<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Events;

use MaxMessenger\Bot\Models\Responses\DialogUnmutedUpdate;

/**
 * @property-read DialogUnmutedUpdate $update
 * @api
 */
final class DialogUnmutedEvent extends BaseEvent
{
}
