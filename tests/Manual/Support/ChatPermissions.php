<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual\Support;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

use function is_string;

/**
 * Названия прав администратора группового чата.
 */
final class ChatPermissions
{
    /**
     * Возвращает название права для пользователя.
     *
     * @param ChatAdminPermission|string $permission Право или его код.
     */
    public static function label(ChatAdminPermission|string $permission): string
    {
        if (is_string($permission)) {
            $permission = ChatAdminPermission::tryFrom($permission) ?? $permission;
        }

        return match ($permission) {
            ChatAdminPermission::ReadAllMessages => 'Читать сообщения',
            ChatAdminPermission::Write => 'Удалять сообщения',
            ChatAdminPermission::PinMessage => 'Закреплять сообщения',
            ChatAdminPermission::ChangeChatInfo => 'Редактировать чат',
            ChatAdminPermission::AddRemoveMembers => 'Добавлять и удалять участников',
            ChatAdminPermission::AddAdmins => 'Назначать и удалять администраторов',
            ChatAdminPermission::EditLink => 'Редактировать ссылку',
            ChatAdminPermission::CanCall => 'Совершать звонки',
            ChatAdminPermission::Edit => 'Редактировать сообщения',
            ChatAdminPermission::Delete => 'Удалять сообщения',
            ChatAdminPermission::ViewStats => 'Просматривать статистику',
            default => $permission,
        };
    }
}
