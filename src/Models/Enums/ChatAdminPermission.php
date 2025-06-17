<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Права администратора чата.
 *
 * Возможные значения:
 * - `read_all_messages` — Читать все сообщения. Это право важно при назначении ботов:
 *   без него бот не будет получать апдейты (вебхуки) в групповом чате.
 * - `add_remove_members` — Добавлять/удалять участников.
 * - `add_admins` — Добавлять администраторов.
 * - `change_chat_info` — Изменять информацию о чате.
 * - `pin_message` — Закреплять сообщения.
 * - `write` — Писать сообщения.
 * - `can_call` — Совершать звонки.
 * - `edit_link` — Изменять ссылку на чат.
 * - `post_edit_delete_message` — Публиковать, редактировать и удалять сообщения.
 * - `edit_message` — Редактировать сообщения.
 * - `delete_message` — Удалять сообщения.
 */
enum ChatAdminPermission: string
{
    use EnumHelperTrait;

    case AddAdmins = 'add_admins';
    case AddRemoveMembers = 'add_remove_members';
    case CanCall = 'can_call';
    case ChangeChatInfo = 'change_chat_info';
    case DeleteMessage = 'delete_message';
    case EditLink = 'edit_link';
    case EditMessage = 'edit_message';
    case PinMessage = 'pin_message';
    case PostEditDeleteMessage = 'post_edit_delete_message';
    case ReadAllMessages = 'read_all_messages';
    case Write = 'write';
}
