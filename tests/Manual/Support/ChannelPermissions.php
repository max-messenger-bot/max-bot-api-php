<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual\Support;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

/**
 * Названия прав администратора канала.
 */
final class ChannelPermissions
{
    /**
     * Возвращает название права для пользователя.
     *
     * @param ChatAdminPermission $permission Право администратора канала.
     */
    public static function label(ChatAdminPermission $permission): string
    {
        return match ($permission) {
            ChatAdminPermission::ReadAllMessages => 'Доступ к постам канала',
            ChatAdminPermission::Write => 'Писать посты',
            ChatAdminPermission::Edit => 'Редактировать чужие посты',
            ChatAdminPermission::Delete => 'Удалять чужие посты',
            ChatAdminPermission::PinMessage => 'Закреплять посты',
            ChatAdminPermission::ChangeChatInfo => 'Редактировать канал',
            ChatAdminPermission::AddRemoveMembers => 'Добавлять и удалять подписчиков',
            ChatAdminPermission::AddAdmins => 'Назначать и удалять администраторов',
            default => $permission->value,
        };
    }
}
