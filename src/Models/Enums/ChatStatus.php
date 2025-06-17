<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Статус чата.
 *
 * Возможные значения:
 * - `active` — Бот является активным участником чата.
 * - `removed` — Бот был удалён из чата.
 * - `left` — Бот покинул чат.
 * - `closed` — Чат был закрыт.
 */
enum ChatStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Left = 'left';
    case Removed = 'removed';
}
