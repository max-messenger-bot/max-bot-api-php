<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;
use MaxMessenger\Bot\Models\Enums\ChatStatus;
use MaxMessenger\Bot\Models\Enums\ChatType;

/**
 * Информация о групповом чате или канале.
 *
 * @link https://dev.max.ru/docs-api/objects/Chat
 */
class Chat extends BaseResponseModel
{
    /**
     * @var array{
     *     chat_id: int,
     *     type: string,
     *     status: string,
     *     title?: non-empty-string,
     *     icon?: array,
     *     last_event_time: int,
     *     participants_count: int,
     *     owner_id?: int,
     *     participants?: array<int, int>,
     *     is_public: bool,
     *     link?: string,
     *     description?: non-empty-string,
     *     dialog_with_user?: array,
     *     chat_message_id?: string,
     *     pinned_message?: array,
     *     messages_count?: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private UserWithPhoto|false|null $dialogWithUser = false;
    private Image|false|null $icon = false;
    private Message|false|null $pinnedMessage = false;

    /**
     * @return int ID чата.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return string|null ID сообщения, содержащего кнопку, через которую был инициирован чат.
     */
    public function getChatMessageId(): ?string
    {
        return $this->data['chat_message_id'] ?? null;
    }

    /**
     * @return non-empty-string|null Описание чата (minLength: 1).
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
     * @return Image|null Иконка чата.
     */
    public function getIcon(): ?Image
    {
        return $this->icon === false
            ? ($this->icon = Image::newFromNullableData($this->data['icon'] ?? null))
            : $this->icon;
    }

    /**
     * @return DateTimeImmutable Время последнего события в чате.
     */
    public function getLastEventTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['last_event_time']);
    }

    /**
     * @return int Время последнего события в чате (Unix timestamp в миллисекундах).
     */
    public function getLastEventTimeRaw(): int
    {
        return $this->data['last_event_time'];
    }

    /**
     * @return string|null Ссылка на чат.
     */
    public function getLink(): ?string
    {
        return $this->data['link'] ?? null;
    }

    /**
     * @return int|null Количество сообщений в чате. Только для групповых чатов и каналов. Недоступно для диалогов.
     */
    public function getMessagesCount(): ?int
    {
        return $this->data['messages_count'] ?? null;
    }

    /**
     * @return int|null ID владельца чата.
     */
    public function getOwnerId(): ?int
    {
        return $this->data['owner_id'] ?? null;
    }

    /**
     * @return int[]|null Участники чата с временем последней активности.
     *     Может быть `null`, если запрашивается список чатов.
     */
    public function getParticipants(): ?array
    {
        return $this->data['participants'] ?? null;
    }

    /**
     * @return int Количество участников чата. Для диалогов всегда 2.
     */
    public function getParticipantsCount(): int
    {
        return $this->data['participants_count'];
    }

    /**
     * @return Message|null Закреплённое сообщение в чате (возвращается только при запросе конкретного чата).
     */
    public function getPinnedMessage(): ?Message
    {
        return $this->pinnedMessage === false
            ? ($this->pinnedMessage = Message::newFromNullableData($this->data['pinned_message'] ?? null))
            : $this->pinnedMessage;
    }

    /**
     * @return ChatStatus|null Статус чата:
     *   - `active` — Бот является активным участником чата.
     *   - `removed` — Бот был удалён из чата.
     *   - `left` — Бот покинул чат.
     *   - `closed` — Чат был закрыт.
     */
    public function getStatus(): ?ChatStatus
    {
        return ChatStatus::tryFrom($this->data['status']);
    }

    /**
     * @return string Статус чата:
     *   - `active` — Бот является активным участником чата.
     *   - `removed` — Бот был удалён из чата.
     *   - `left` — Бот покинул чат.
     *   - `closed` — Чат был закрыт.
     */
    public function getStatusRaw(): string
    {
        return $this->data['status'];
    }

    /**
     * @return non-empty-string|null Отображаемое название чата (minLength: 1). Может быть `null` для диалогов.
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
     * @return string Тип чата.
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
