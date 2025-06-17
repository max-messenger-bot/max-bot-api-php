<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\UserAddedUpdate;

/**
 * @property-read UserAddedUpdate $update
 * @api
 */
final class UserAddedEvent extends BaseEvent
{
}
