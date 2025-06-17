<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Events;

use MaxMessenger\Bot\Models\Responses\MessageRemovedUpdate;

/**
 * @property-read MessageRemovedUpdate $update
 * @api
 */
final class MessageRemovedEvent extends BaseEvent
{
}
