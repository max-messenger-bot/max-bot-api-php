<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use DateTimeImmutable;
use MaxMessenger\Bot\Models\Enums\ChatStatus;
use MaxMessenger\Bot\Models\Enums\ChatType;

/**
 * Chat information.
 *
 * @api
 */
readonly class Chat extends BaseResponseModel
{
    /**
     * @var array{
     *     chat_id: int,
     *     chat_message_id?: string|null,
     *     description: string|null,
     *     dialog_with_user?: array|null,
     *     icon: array|null,
     *     is_public: bool,
     *     last_event_time: int,
     *     link?: string|null,
     *     messages_count?: int|null,
     *     owner_id?: int|null,
     *     participants?: int[]|null,
     *     participants_count: int,
     *     pinned_message?: array|null,
     *     status: string,
     *     title: string|null,
     *     type: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return int Chats identifier.
     * @api
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return string|null Identifier of message that contains `chat` button initialized chat.
     * @api
     */
    public function getChatMessageId(): ?string
    {
        return $this->data['chat_message_id'] ?? null;
    }

    /**
     * @return string|null Chat description.
     * @api
     */
    public function getDescription(): ?string
    {
        return $this->data['description'];
    }

    /**
     * @return UserWithPhoto|null Another user in conversation. For `dialog` type chats only.
     * @api
     */
    public function getDialogWithUser(): ?UserWithPhoto
    {
        return UserWithPhoto::newFromNullableData($this->data['dialog_with_user'] ?? null);
    }

    /**
     * @return Image|null Icon of chat.
     * @api
     */
    public function getIcon(): ?Image
    {
        return Image::newFromNullableData($this->data['icon']);
    }

    /**
     * @return DateTimeImmutable Time of last event occurred in chat.
     * @api
     */
    public function getLastEventTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['last_event_time']);
    }

    /**
     * @return int Time of last event occurred in chat (Unix timestamp in milliseconds).
     * @api
     */
    public function getLastEventTimeRaw(): int
    {
        return $this->data['last_event_time'];
    }

    /**
     * @return string|null Link on chat.
     * @api
     */
    public function getLink(): ?string
    {
        return $this->data['link'] ?? null;
    }

    /**
     * @return int|null Messages count in chat. Only for group chats and channels. **Not available** for dialogs.
     * @api
     */
    public function getMessagesCount(): ?int
    {
        return $this->data['messages_count'] ?? null;
    }

    /**
     * @return int|null Identifier of chat owner. Visible only for chat admins.
     * @api
     */
    public function getOwnerId(): ?int
    {
        return $this->data['owner_id'] ?? null;
    }

    /**
     * @return int[]|null Participants in chat with time of last activity.
     *     Can be *null* when you request list of chats. Visible for chat admins only.
     * @api
     */
    public function getParticipants(): ?array
    {
        return $this->data['participants'] ?? null;
    }

    /**
     * @return int Number of people in chat. Always 2 for `dialog` chat type.
     * @api
     */
    public function getParticipantsCount(): int
    {
        return $this->data['participants_count'];
    }

    /**
     * @return Message|null Pinned message in chat or channel. Returned only when single chat is requested.
     * @api
     */
    public function getPinnedMessage(): ?Message
    {
        return Message::newFromNullableData($this->data['pinned_message'] ?? null);
    }

    /**
     * @return ChatStatus Chat status. One of:
     *   - active: bot is active member of chat;
     *   - removed: bot was kicked;
     *   - left: bot intentionally left chat;
     *   - closed: chat was closed;
     *   - suspended: bot was stopped by user. *Only for dialogs*.
     * @api
     */
    public function getStatus(): ChatStatus
    {
        return ChatStatus::from($this->data['status']);
    }

    /**
     * @return string Chat status. One of:
     *   - active: bot is active member of chat;
     *   - removed: bot was kicked;
     *   - left: bot intentionally left chat;
     *   - closed: chat was closed;
     *   - suspended: bot was stopped by user. *Only for dialogs*.
     * @api
     */
    public function getStatusRaw(): string
    {
        return $this->data['status'];
    }

    /**
     * @return string|null Visible title of chat. Can be null for dialogs.
     * @api
     */
    public function getTitle(): ?string
    {
        return $this->data['title'];
    }

    /**
     * @return ChatType Type of chat. One of: dialog, chat, channel.
     * @api
     */
    public function getType(): ChatType
    {
        return ChatType::from($this->data['type']);
    }

    /**
     * @return string Type of chat. One of: dialog, chat, channel.
     * @api
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    /**
     * @return bool Is current chat publicly available. Always `false` for dialogs.
     * @api
     */
    public function isPublic(): bool
    {
        return $this->data['is_public'];
    }
}
