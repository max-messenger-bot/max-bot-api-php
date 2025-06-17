<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Тип связанного сообщения.
 */
enum MessageLinkType: string
{
    case Forward = 'forward';
    case Reply = 'reply';
}
