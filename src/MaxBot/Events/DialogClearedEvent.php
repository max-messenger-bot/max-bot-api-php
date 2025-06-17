<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Events;

use MaxMessenger\Bot\Models\Responses\DialogClearedUpdate;

/**
 * @property-read DialogClearedUpdate $update
 * @api
 */
final class DialogClearedEvent extends BaseEvent
{
}
