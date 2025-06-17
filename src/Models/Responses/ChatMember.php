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
 * Возвращается только при вызове некоторых методов группы /chats, например {@see MaxApiClient::getMembers()}
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
     * @return string|null Заголовок, который будет показан на клиенте. Если пользователь администратор или владелец
     *     и ему не установлено это название, то поле не передаётся, клиенты на своей стороне подменят
     *     на "владелец" или "админ".
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
     * @return list<ChatAdminPermission>|null Перечень прав пользователя. Возможные значения:
     *   - read_all_messages — Читать все сообщения.
     *   - add_remove_members — Добавлять/удалять участников.
     *   - add_admins — Добавлять администраторов.
     *   - change_chat_info — Изменять информацию о чате.
     *   - pin_message — Закреплять сообщения.
     *   - write — Писать сообщения.
     *   - edit_link — Изменять ссылку на чат.
     */
    public function getPermissions(): ?array
    {
        return ChatAdminPermission::tryFromNullableList($this->data['permissions'] ?? null);
    }

    /**
     * @return list<string>|null Перечень прав пользователя. Возможные значения:
     *   - read_all_messages — Читать все сообщения.
     *   - add_remove_members — Добавлять/удалять участников.
     *   - add_admins — Добавлять администраторов.
     *   - change_chat_info — Изменять информацию о чате.
     *   - pin_message — Закреплять сообщения.
     *   - write — Писать сообщения.
     *   - edit_link — Изменять ссылку на чат.
     */
    public function getPermissionsRaw(): ?array
    {
        return $this->data['permissions'] ?? null;
    }

    /**
     * @return bool Является ли пользователь администратором чата.
     */
    public function isAdmin(): bool
    {
        return $this->data['is_admin'];
    }

    /**
     * @return bool Является ли пользователь владельцем чата.
     */
    public function isOwner(): bool
    {
        return $this->data['is_owner'];
    }
}
