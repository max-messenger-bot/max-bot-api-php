<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use DateTimeImmutable;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

use function in_array;

/**
 * Информация о пользователе или боте, включая URL аватара и описание (при наличии).
 *
 * Дополнительно содержит данные для пользователей-участников чата или канала.
 * Возвращается только при вызове некоторых методов группы `/chats`, например {@see MaxApiClient::getMembers()}
 *
 * @link https://dev.max.ru/docs-api/objects/ChatMember
 */
class ChatMember extends UserWithPhoto
{
    /**
     * @var array{
     *     last_access_time: non-negative-int,
     *     is_owner: bool,
     *     is_admin: bool,
     *     join_time: non-negative-int,
     *     permissions?: list<non-empty-string>,
     *     alias?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string|null Псевдоним участника в чате (maxLength: 16). Может быть только у администратора.
     */
    public function getAlias(): ?string
    {
        return $this->data['alias'] ?? null;
    }

    /**
     * @return DateTimeImmutable Дата присоединения к чату.
     */
    public function getJoinTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['join_time']);
    }

    /**
     * @return non-negative-int Дата присоединения к чату (Unix-время в миллисекундах).
     */
    public function getJoinTimeRaw(): int
    {
        return $this->data['join_time'];
    }

    /**
     * @return DateTimeImmutable Время последней активности пользователя в чате или канале.
     *     Может быть устаревшим для суперчатов и каналов (равно времени вступления).
     */
    public function getLastAccessTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['last_access_time']);
    }

    /**
     * @return non-negative-int Время последней активности пользователя в чате или канале (Unix-время в миллисекундах).
     *     Может быть устаревшим для суперчатов и каналов (равно времени вступления).
     */
    public function getLastAccessTimeRaw(): int
    {
        return $this->data['last_access_time'];
    }

    /**
     * @return list<ChatAdminPermission>|null Перечень прав доступа пользователя или бота, если тот является
     *     администратором группового чата или канала. Для обычных участников чата или канала поле не возвращается.
     */
    public function getPermissions(): ?array
    {
        return ChatAdminPermission::tryFromNullableList($this->data['permissions'] ?? null);
    }

    /**
     * @return list<non-empty-string>|null Перечень прав доступа пользователя или бота, если тот является
     *     администратором группового чата или канала. Для обычных участников чата или канала поле не возвращается.
     *     Значения описаны в {@see ChatAdminPermission}.
     */
    public function getPermissionsRaw(): ?array
    {
        return $this->data['permissions'] ?? null;
    }

    /**
     * Проверяет наличие права администратора у участника.
     *
     * @param ChatAdminPermission $permission Проверяемое право.
     */
    public function hasPermission(ChatAdminPermission $permission): bool
    {
        return in_array($permission->value, $this->data['permissions'] ?? [], true);
    }

    /**
     * @return bool Является ли пользователь администратором группового чата или канала.
     */
    public function isAdmin(): bool
    {
        return $this->data['is_admin'];
    }

    /**
     * @return bool Является ли пользователь владельцем группового чата или канала.
     */
    public function isOwner(): bool
    {
        return $this->data['is_owner'];
    }
}
