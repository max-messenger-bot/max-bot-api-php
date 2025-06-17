<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Enums\ChatAdminPermission;

/**
 * Объект включает общую информацию о пользователе или боте, URL аватара и описание (при наличии).
 *
 * Дополнительно содержит данные для пользователей-участников чата.
 * Возвращается только при вызове некоторых методов группы `chats`, например {@see MaxApiClient::getMembers()}.
 *
 * @link https://dev.max.ru/docs-api/objects/ChatMember
 * @api
 */
class ChatMember extends UserWithPhoto
{
    /**
     * @var array{
     *     last_access_time: int,
     *     is_owner: bool,
     *     is_admin: bool,
     *     join_time: int,
     *     permissions?: list<string>|null,
     *     alias?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string|null Заголовок, который будет показан на клиенте.
     *     Если пользователь администратор или владелец и ему не установлено это название,
     *     то поле не передаётся, клиенты на своей стороне подменят на `владелец` или `админ`.
     * @api
     */
    public function getAlias(): ?string
    {
        return $this->data['alias'] ?? null;
    }

    /**
     * @return DateTimeImmutable Время присоединения к чату.
     * @api
     */
    public function getJoinTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['join_time']);
    }

    /**
     * @return int Время присоединения к чату в формате Unix-time.
     * @api
     */
    public function getJoinTimeRaw(): int
    {
        return $this->data['join_time'];
    }

    /**
     * @return DateTimeImmutable Время последней активности пользователя в чате.
     *     Может быть устаревшим для суперчатов (равно времени `join_time`).
     * @api
     */
    public function getLastAccessTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['last_access_time']);
    }

    /**
     * @return int Время последней активности пользователя в чате.
     *     Может быть устаревшим для суперчатов (равно времени `join_time`).
     * @api
     */
    public function getLastAccessTimeRaw(): int
    {
        return $this->data['last_access_time'];
    }

    /**
     * @return list<ChatAdminPermission>|null Перечень прав пользователя.
     *     Возможные значения {@see ChatAdminPermission}.
     * @api
     */
    public function getPermissions(): ?array
    {
        return ChatAdminPermission::tryFromNullableList($this->data['permissions'] ?? null);
    }

    /**
     * @return list<string>|null Перечень прав пользователя.
     *     Возможные значения {@see ChatAdminPermission}.
     * @api
     */
    public function getPermissionsRaw(): ?array
    {
        return $this->data['permissions'] ?? null;
    }

    /**
     * @return bool `true` если пользователь является администратором чата.
     * @api
     */
    public function isAdmin(): bool
    {
        return $this->data['is_admin'];
    }

    /**
     * @return bool `true` если пользователь является владельцем чата.
     * @api
     */
    public function isOwner(): bool
    {
        return $this->data['is_owner'];
    }
}
