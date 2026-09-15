<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

use MaxMessenger\Bot\Model\Response\Update;

/**
 * Тип события.
 *
 * Объект {@see Update} представляет различные типы событий, произошедших в чате.
 *
 * > Чтобы получать события из группового чата или канала, назначьте бота администратором
 * > и дайте права на чтение всех сообщений.
 *
 * На свои действия бот события не получает.
 */
enum UpdateType: string
{
    use EnumHelperTrait;

    case BotAdded = 'bot_added';
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
