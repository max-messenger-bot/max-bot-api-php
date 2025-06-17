<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Responses\MessageCallbackUpdate;

/**
 * @property-read MessageCallbackUpdate $update
 * @api
 */
final class MessageCallbackEvent extends BaseEvent
{
}
