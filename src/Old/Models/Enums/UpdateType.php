<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Enums;

enum UpdateType: string
{
    case BotAdded = 'bot_added';
    case BotRemoved = 'bot_removed';
    case BotStarted = 'bot_started';
    case ChatTitleChanged = 'chat_title_changed';
    case MessageCallback = 'message_callback';
    case MessageChatCreated = 'message_chat_created';
    case MessageCreated = 'message_created';
    case MessageEdited = 'message_edited';
    case MessageRemoved = 'message_removed';
    case NotSet = '';
    case Other = '?';
    case UserAdded = 'user_added';
    case UserRemoved = 'user_removed';
}
