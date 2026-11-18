<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Статус чата.
 *
 * Статус для текущего бота:
 * - `active` — Бот является активным участником чата.
 * - `removed` — Бот был удалён из чата.
 * - `left` — Бот самостоятельно покинул чат.
 * - `closed` — Чат был закрыт.
 * - `suspended` — Пользователь остановил бота. Только для диалогов.
 *
 * @link https://dev.max.ru/docs-api/objects/Chat
 * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/
 */
enum ChatStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Left = 'left';
    case Removed = 'removed';
    case Suspended = 'suspended';
}
