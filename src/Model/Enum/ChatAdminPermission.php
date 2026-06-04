<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Права администратора группового чата или канала.
 *
 * Краткое описание доступных прав администратора:
 * - `read_all_messages` — читать все сообщения в канале или групповом чате. Это право важно при назначении ботов:
 *   без него бот не будет получать события группового чата или канала.
 * - `edit` — редактировать посты в каналах (для групповых чатов недоступно). Ранее вместо `edit` в API использовалось
 *   `edit_message` — в ответе могут возвращаться оба значения, однако при назначении новых прав администраторам
 *   используйте `edit`.
 * - `delete` — удалять посты (для групповых чатов недоступно). Ранее вместо `delete` в API использовалось
 *   `delete_message` — в ответе могут возвращаться оба значения, однако при назначении новых прав администраторам
 *   используйте `delete`.
 * - `write` — редактировать и удалять сообщения в групповых чатах, а также писать посты в каналах. Ранее вместо `write`
 *   в API использовалось `post_edit_delete_message` — в ответе могут возвращаться оба значения, однако при назначении
 *   новых прав администраторам используйте `write`.
 * - `pin_message` — закреплять сообщение.
 * - `change_chat_info` — изменять информацию о канале или групповом чате.
 * - `add_remove_members` — добавлять и удалять участников группового чата или подписчиков канала.
 * - `add_admins` — добавлять и удалять администраторов группового чата или канала.
 * - `edit_link` — изменять ссылку на групповой чат (для каналов недоступно).
 * - `can_call` — звонить в групповом чате (для каналов недоступно).
 * - `view_stats` — видеть количество просмотров постов в каналах (для групповых чатов недоступно). Право есть только у
 *   пользователей — боты не могут смотреть статистику.
 * - `delete_message` — удалять посты (для групповых чатов недоступно). Устаревшее значение.
 * - `edit_message` — редактировать посты в каналах (для групповых чатов недоступно). Устаревшее значение.
 * - `post_edit_delete_message` — редактировать и удалять сообщения в групповых чатах, а также писать посты в каналах.
 *   Устаревшее значение.
 */
enum ChatAdminPermission: string
{
    use EnumHelperTrait;

    case AddAdmins = 'add_admins';
    case AddRemoveMembers = 'add_remove_members';
    case CanCall = 'can_call';
    case ChangeChatInfo = 'change_chat_info';
    case Delete = 'delete';
    case DeleteMessage = 'delete_message';
    case Edit = 'edit';
    case EditLink = 'edit_link';
    case EditMessage = 'edit_message';
    case PinMessage = 'pin_message';
    case PostEditDeleteMessage = 'post_edit_delete_message';
    case ReadAllMessages = 'read_all_messages';
    case ViewStats = 'view_stats';
    case Write = 'write';
}
