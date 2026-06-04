<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use DateTimeImmutable;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

/**
 * Объект включает общую информацию о пользователе или боте, URL аватара и описание (при наличии).
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
     *     last_access_time: int,
     *     is_owner: bool,
     *     is_admin: bool,
     *     join_time: int,
     *     permissions?: list<non-empty-string>,
     *     alias?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string|null Описание роли, которое будет отображаться на клиентском устройстве в настройках
     *     чата или канала рядом с именем пользователя. Если пользователь администратор или владелец и ему
     *     не установлено это название, то поле не передаётся, клиентское устройство на своей стороне подменит
     *     значение на соответствующее: "владелец" или "админ".
     */
    public function getAlias(): ?string
    {
        return $this->data['alias'] ?? null;
    }

    /**
     * @return DateTimeImmutable Дата присоединения к чату в формате Unix-time.
     */
    public function getJoinTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['join_time']);
    }

    /**
     * @return int Дата присоединения к чату в формате Unix-time.
     */
    public function getJoinTimeRaw(): int
    {
        return $this->data['join_time'];
    }

    /**
     * @return DateTimeImmutable Время последней активности пользователя в чате.
     *     Может быть устаревшим для суперчатов (равно времени вступления).
     */
    public function getLastAccessTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['last_access_time']);
    }

    /**
     * @return int Время последней активности пользователя в чате.
     *     Может быть устаревшим для суперчатов (равно времени вступления).
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
     * @return list<string>|null Перечень прав доступа пользователя или бота, если тот является
     *     администратором группового чата или канала. Для обычных участников чата или канала поле не возвращается.
     */
    public function getPermissionsRaw(): ?array
    {
        return $this->data['permissions'] ?? null;
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
