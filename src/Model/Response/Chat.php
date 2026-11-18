<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use DateTimeImmutable;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\ChatStatus;
use MaxMessenger\Bot\Model\Enum\ChatType;

/**
 * Информация о групповом чате, канале или диалоге: его тип, настройки отображения
 * (название, аватар, описание, ссылка), публичную доступность, а также информацию об участниках
 * (владельце, боте и других пользователях), времени их последней активности и событиях.
 *
 * @link https://dev.max.ru/docs-api/objects/Chat
 */
class Chat extends BaseResponseModel
{
    /**
     * @var array{
     *     chat_id: int,
     *     type: non-empty-string,
     *     status: non-empty-string,
     *     title?: non-empty-string,
     *     icon?: array,
     *     last_event_time: non-negative-int,
     *     participants_count: non-negative-int,
     *     owner_id?: int,
     *     participants?: array<int, non-negative-int>,
     *     is_public: bool,
     *     link?: non-empty-string,
     *     description?: non-empty-string,
     *     dialog_with_user?: array,
     *     messages_count?: non-negative-int,
     *     chat_message_id?: non-empty-string,
     *     pinned_message?: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private UserWithPhoto|false|null $dialogWithUser = false;
    private Image|false|null $icon = false;
    private Message|false|null $pinnedMessage = false;

    /**
     * @return int Идентификатор чата или канала.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * Идентификатор сообщения с кнопкой, через которую был инициирован чат.
     *
     * Это не `mid`: значение приходит строкой base64url без выравнивающих символов `=`, которая декодируется
     * в 26 байт — `0x01` (версия), `chat_id` (int64, big-endian), `seq` (int64, big-endian), `0x01`,
     * `user_id` отправителя сообщения (int64, big-endian). Первые два поля — те же `chat_id` и `seq`,
     * из которых состоит `mid` сообщения.
     *
     * Подставлять это значение в {@see MaxApiClient::getMessages()} нельзя: сервер вернёт пустой список
     * без ошибки. Сначала его нужно преобразовать в `mid`.
     *
     * Извлечение `chat_id`, `seq`, `user_id` и `mid`:
     * ```
     * $raw = base64_decode(strtr($chat->getChatMessageId(), '-_', '+/'), true);
     * ['chatId' => $chatId, 'seq' => $seq, 'userId' => $userId] = unpack('x/JchatId/Jseq/x/JuserId', $raw);
     * $mid = 'mid.' . bin2hex(substr($raw, 1, 16));
     * ```
     *
     * @return non-empty-string|null Идентификатор сообщения с кнопкой, через которую был инициирован чат.
     */
    public function getChatMessageId(): ?string
    {
        return $this->data['chat_message_id'] ?? null;
    }

    /**
     * @return non-empty-string|null Описание чата или канала.
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return UserWithPhoto|null Данные о пользователе в диалоге (только для чатов типа `dialog`).
     */
    public function getDialogWithUser(): ?UserWithPhoto
    {
        return $this->dialogWithUser === false
            ? ($this->dialogWithUser = UserWithPhoto::newFromNullableData($this->data['dialog_with_user'] ?? null))
            : $this->dialogWithUser;
    }

    /**
     * @return Image|null Иконка чата или канала.
     */
    public function getIcon(): ?Image
    {
        return $this->icon === false
            ? ($this->icon = Image::newFromNullableData($this->data['icon'] ?? null))
            : $this->icon;
    }

    /**
     * @return DateTimeImmutable Время последнего события в чате или канале.
     */
    public function getLastEventTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['last_event_time']);
    }

    /**
     * @return non-negative-int Время последнего события в чате или канале (Unix-время в миллисекундах).
     */
    public function getLastEventTimeRaw(): int
    {
        return $this->data['last_event_time'];
    }

    /**
     * @return non-empty-string|null Ссылка на чат.
     */
    public function getLink(): ?string
    {
        return $this->data['link'] ?? null;
    }

    /**
     * @return non-negative-int|null Количество сообщений в групповых чатах и каналах. Недоступно для диалогов.
     */
    public function getMessagesCount(): ?int
    {
        return $this->data['messages_count'] ?? null;
    }

    /**
     * @return int|null Идентификатор владельца чата или канала. Доступен только администраторам чата.
     */
    public function getOwnerId(): ?int
    {
        return $this->data['owner_id'] ?? null;
    }

    /**
     * @return array<int, non-negative-int>|null Участники чата или канала с временем последней активности.
     *     Ключи — идентификаторы пользователей, значения — Unix-время в миллисекундах или `0`.
     *     Доступно только администраторам.
     */
    public function getParticipants(): ?array
    {
        return $this->data['participants'] ?? null;
    }

    /**
     * @return non-negative-int Количество участников чата или канала. Для диалогов всегда 2.
     */
    public function getParticipantsCount(): int
    {
        return $this->data['participants_count'];
    }

    /**
     * @return Message|null Закреплённое сообщение в чате
     *     (возвращается только при запросе конкретного чата или канала).
     */
    public function getPinnedMessage(): ?Message
    {
        return $this->pinnedMessage === false
            ? ($this->pinnedMessage = Message::newFromNullableData($this->data['pinned_message'] ?? null))
            : $this->pinnedMessage;
    }

    /**
     * @return ChatStatus|null Статус чата.
     */
    public function getStatus(): ?ChatStatus
    {
        return ChatStatus::tryFrom($this->data['status']);
    }

    /**
     * @return non-empty-string Статус чата для текущего бота. Значения описаны в {@see ChatStatus}.
     */
    public function getStatusRaw(): string
    {
        return $this->data['status'];
    }

    /**
     * @return non-empty-string|null Отображаемое название чата или канала.
     *     Может отсутствовать для диалогов.
     */
    public function getTitle(): ?string
    {
        return $this->data['title'] ?? null;
    }

    /**
     * @return ChatType|null Тип чата.
     */
    public function getType(): ?ChatType
    {
        return ChatType::tryFrom($this->data['type']);
    }

    /**
     * @return non-empty-string Тип чата. Значения описаны в {@see ChatType}.
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    /**
     * @return bool Доступен ли чат публично (для диалогов всегда `false`).
     */
    public function isPublic(): bool
    {
        return $this->data['is_public'];
    }
}
