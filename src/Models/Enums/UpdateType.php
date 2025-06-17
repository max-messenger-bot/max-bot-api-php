<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Тип обновления.
 *
 * Объект `Update` представляет различные типы событий, произошедших в чате.
 *
 * > Чтобы получать события из группового чата или канала, назначьте бота
 * администратором и дайте права на чтение всех сообщений.
 */
enum UpdateType: string
{
    use EnumHelperTrait;

    case BotAdded = 'bot_added';
    case BotRemoved = 'bot_removed';
    case BotStarted = 'bot_started';
    case BotStopped = 'bot_stopped';
    case ChatTitleChanged = 'chat_title_changed';
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
