<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Перечень прав администратора чата.
 *
 * Возможные значения:
 * - `add_admins` - Добавлять администраторов.
 * - `add_remove_members` - Добавлять/удалять участников.
 * - `can_call` -
 * - `change_chat_info` — Изменять информацию о чате.
 * - `delete` -
 * - `edit` -
 * - `edit_link` — Изменять ссылку на чат.
 * - `pin_message` — Закреплять сообщения.
 * - `read_all_messages` - Читать все сообщения.
 * - `view_stats` -
 * - `write` — Писать сообщения.
 *
 * @link https://dev.max.ru/docs-api/objects/ChatMember
 * @api
 */
enum ChatAdminPermission: string
{
    use EnumHelperTrait;

    case AddAdmins = 'add_admins';
    case AddRemoveMembers = 'add_remove_members';
    case CanCall = 'can_call';
    case ChangeChatInfo = 'change_chat_info';
    case Delete = 'delete';
    case Edit = 'edit';
    case EditLink = 'edit_link';
    case PinMessage = 'pin_message';
    case ReadAllMessages = 'read_all_messages';
    case ViewStats = 'view_stats';
    case Write = 'write';
}
