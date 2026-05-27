<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип чата.
 *
 * Возможные значения:
 * - `chat` — Групповой чат.
 * - `channel` — Канал.
 * - `dialog` — Диалог.
 */
enum ChatType: string
{
    case Channel = 'channel';
    case Chat = 'chat';
    case Dialog = 'dialog';
}
