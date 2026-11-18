<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

use MaxMessenger\Bot\Model\Response\Update;

/**
 * Тип события.
 *
 * События в объекте {@see Update}:
 * - `bot_added` — Бот добавлен в чат или канал.
 * - `bot_admin_permissions_changed` — Изменены права бота-администратора в чате или канале.
 * - `bot_removed` — Бот удалён из чата или канала.
 * - `bot_started` — Пользователь начал или возобновил общение с ботом.
 * - `bot_stopped` — Пользователь остановил или удалил бота.
 * - `chat_title_changed` — Изменено название чата.
 * - `comment_created` — Опубликован новый комментарий.
 * - `comment_edited` — Изменён комментарий.
 * - `comment_removed` — Удалён комментарий.
 * - `dialog_cleared` — Пользователь очистил историю диалога с ботом.
 * - `dialog_muted` — Пользователь отключил уведомления в диалоге с ботом.
 * - `dialog_removed` — Пользователь удалил диалог с ботом. Одновременно возвращается `bot_stopped`.
 * - `dialog_unmuted` — Пользователь включил уведомления в диалоге с ботом.
 * - `message_callback` — Пользователь нажал callback-кнопку.
 * - `message_created` — Создано сообщение или опубликован пост.
 * - `message_edited` — Изменено сообщение или пост.
 * - `message_removed` — Удалено сообщение или пост.
 * - `user_added` — Пользователь добавлен в чат или канал либо присоединился по ссылке.
 * - `user_removed` — Пользователь удалён из чата или канала либо покинул его.
 *
 * @link https://dev.max.ru/docs-api/objects/Update
 * @link https://dev.max.ru/docs-api/use-cases/event-notifications
 * @link https://dev.max.ru/docs-api/methods/POST/subscriptions/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
enum UpdateType: string
{
    use EnumHelperTrait;

    case BotAdded = 'bot_added';
    case BotAdminPermissionsChanged = 'bot_admin_permissions_changed';
    case BotRemoved = 'bot_removed';
    case BotStarted = 'bot_started';
    case BotStopped = 'bot_stopped';
    case ChatTitleChanged = 'chat_title_changed';
    case CommentCreated = 'comment_created';
    case CommentEdited = 'comment_edited';
    case CommentRemoved = 'comment_removed';
    case DialogCleared = 'dialog_cleared';
    case DialogMuted = 'dialog_muted';
    case DialogRemoved = 'dialog_removed';
    case DialogUnmuted = 'dialog_unmuted';
    case MessageCallback = 'message_callback';
    case MessageCreated = 'message_created';
    case MessageEdited = 'message_edited';
    case MessageRemoved = 'message_removed';
    case UserAdded = 'user_added';
    case UserRemoved = 'user_removed';
}
