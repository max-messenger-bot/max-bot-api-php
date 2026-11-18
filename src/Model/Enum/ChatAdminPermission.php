<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Права администратора.
 *
 * **Общие**:
 *
 * - `read_all_messages` — Право читать все сообщения в групповом чате или посты в канале.
 *   Без этого права нельзя назначить права `pin_message`, `edit`, `delete` и `write`.
 *   Также это право важно при назначении ботов: без него бот не будет получать события группового чата или канала.
 *   Управление `read_all_messages` в интерфейсе мессенджера доступно только для ботов в групповых чатах.
 * - `write` — Право удалять чужие сообщения в групповых чатах, а также писать посты и комментарии в каналах.
 *   Для назначения требуется право `read_all_messages`, выданное ранее или одновременно.
 * - `pin_message` — Право закреплять сообщение.
 *   Для назначения требуется право `read_all_messages`, выданное ранее или одновременно.
 * - `change_chat_info` — Право изменять информацию о канале или групповом чате.
 * - `add_remove_members` — Право добавлять и удалять участников группового чата или подписчиков канала.
 * - `add_admins` — Право добавлять и удалять администраторов группового чата или канала.
 *
 * **Групповые чаты**:
 *
 * - `edit_link` — Право изменять ссылку на групповой чат (для каналов недоступно).
 * - `can_call` — Право звонить в групповом чате (для каналов недоступно). Право проставляется автоматически при
 *   назначении администратором. Управление `can_call` в интерфейсе мессенджера не дублируется.
 *
 * **Каналы**:
 *
 * - `edit` — Право редактировать посты и комментарии в каналах (для групповых чатов недоступно).
 *   Для назначения требуется право `read_all_messages`, выданное ранее или одновременно.
 * - `delete` — Право удалять посты и комментарии в каналах (для групповых чатов недоступно).
 *   Для назначения требуется право `read_all_messages`, выданное ранее или одновременно.
 * - `view_stats` — Право на просмотр статистики каналов (для групповых чатов недоступно).
 *   Назначается по умолчанию владельцам каналов. Для других пользователей-администраторов канала и ботов недоступно.
 *
 * @link https://dev.max.ru/docs-api/objects/ChatMember
 * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members/admins/
 * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/admins/
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
