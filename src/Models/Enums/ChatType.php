<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Тип чата: диалог, чат, канал.
 */
enum ChatType: string
{
    case Channel = 'channel';
    case Chat = 'chat';
    case Dialog = 'dialog';
}
