<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Type of linked message.
 *
 * @api
 */
enum MessageLinkType: string
{
    case Forward = 'forward';
    case Reply = 'reply';
}
