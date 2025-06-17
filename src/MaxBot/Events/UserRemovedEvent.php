<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\UserRemovedUpdate;

/**
 * @property-read UserRemovedUpdate $update
 * @api
 */
final class UserRemovedEvent extends BaseEvent
{
}
