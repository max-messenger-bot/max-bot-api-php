<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\BotAdminPermissionsChangedUpdate;

/**
 * Событие изменения прав бота-администратора группового чата или канала.
 *
 * Доставляется только через Webhook; Long Polling пока не поддерживается.
 *
 * @property-read BotAdminPermissionsChangedUpdate $update
 */
final class BotAdminPermissionsChangedEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;

    /**
     * @return int Идентификатор бота, чьи права администратора были изменены.
     */
    public function getBotId(): int
    {
        return $this->update->getBotId();
    }

    /**
     * @return int Идентификатор группового чата или канала, в котором изменились права бота-администратора.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return list<ChatAdminPermission>|null Права бота-администратора.
     */
    public function getPermissions(): ?array
    {
        return $this->update->getPermissions();
    }

    /**
     * @return list<non-empty-string>|null Права бота-администратора. Значения описаны в {@see ChatAdminPermission}.
     */
    public function getPermissionsRaw(): ?array
    {
        return $this->update->getPermissionsRaw();
    }

    /**
     * @return null У события нет пользователя.
     */
    public function getUser(): null
    {
        return null;
    }

    /**
     * @return int Идентификатор пользователя или бота, который изменил права администратора.
     */
    public function getUserId(): int
    {
        return $this->update->getUserId();
    }

    /**
     * @return bool Признак того, является ли бот администратором канала или группового чата или нет.
     */
    public function isAdmin(): bool
    {
        return $this->update->isAdmin();
    }

    /**
     * @return bool Признак того, что событие произошло в канале.
     */
    public function isChannel(): bool
    {
        return $this->update->isChannel();
    }
}
