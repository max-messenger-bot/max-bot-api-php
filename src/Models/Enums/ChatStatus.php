<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Chat status.
 *
 * One of:
 * - active: bot is active member of chat;
 * - removed: bot was kicked;
 * - left: bot intentionally left chat;
 * - closed: chat was closed;
 * - suspended: bot was stopped by user. *Only for dialogs*.
 *
 * @api
 */
enum ChatStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Left = 'left';
    case Removed = 'removed';
    case Suspended = 'suspended';
}
