<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

/**
 * Событие изменения прав бота-администратора группового чата или канала.
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class BotAdminPermissionsChangedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user_id: int,
     *     bot_id: int,
     *     is_channel: bool,
     *     is_admin: bool,
     *     permissions?: list<non-empty-string>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Идентификатор бота, чьи права администратора были изменены.
     */
    public function getBotId(): int
    {
        return $this->data['bot_id'];
    }

    /**
     * @return int Идентификатор группового чата или канала, в котором изменились права бота-администратора.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return list<ChatAdminPermission>|null Обновлённые права бота-администратора.
     */
    public function getPermissions(): ?array
    {
        return ChatAdminPermission::tryFromNullableList($this->data['permissions'] ?? null);
    }

    /**
     * @return list<non-empty-string>|null Обновлённые права бота-администратора.
     *     Значения описаны в {@see ChatAdminPermission}.
     */
    public function getPermissionsRaw(): ?array
    {
        return $this->data['permissions'] ?? null;
    }

    /**
     * @return list<non-empty-string> Имена обязательных свойств обновления.
     */
    public function getRequiredProperties(): array
    {
        return ['chat_id', 'user_id', 'bot_id', 'is_channel', 'is_admin'];
    }

    /**
     * @return int Идентификатор пользователя или бота, который изменил права администратора.
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }

    /**
     * @return bool Признак того, является ли бот администратором канала или группового чата или нет.
     */
    public function isAdmin(): bool
    {
        return $this->data['is_admin'];
    }

    /**
     * @return bool Признак того, что событие произошло в канале.
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
