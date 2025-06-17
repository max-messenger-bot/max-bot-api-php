<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Type of chat.
 *
 * Dialog (one-on-one), chat or channel.
 *
 * @api
 */
enum ChatType: string
{
    case Channel = 'channel';
    case Chat = 'chat';
    case Dialog = 'dialog';
}
