<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип чата.
 *
 * - `chat` — Групповой чат.
 * - `channel` — Канал.
 * - `dialog` — Диалог.
 *
 * Для комментариев к постам в канале значение определяет, от чьего имени оставлен комментарий:
 * - `channel` — От имени канала.
 * - `chat` — От имени пользователя или бота.
 *
 * @link https://dev.max.ru/docs-api/objects/Chat
 * @link https://dev.max.ru/docs-api/objects/Message
 * @link https://dev.max.ru/docs-api/objects/CommentMessage
 * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/
 */
enum ChatType: string
{
    case Channel = 'channel';
    case Chat = 'chat';
    case Dialog = 'dialog';
}
