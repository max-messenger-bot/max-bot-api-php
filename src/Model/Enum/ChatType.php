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
 *
 * Для комментариев к постам в канале значение определяет, от чьего имени оставлен комментарий:
 * - `channel` — от имени канала.
 * - `chat` — от имени пользователя или бота.
 */
enum ChatType: string
{
    case Channel = 'channel';
    case Chat = 'chat';
    case Dialog = 'dialog';
}
