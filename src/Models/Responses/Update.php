<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;
use MaxMessenger\Bot\Exceptions\Validation\RequiredFieldException;
use MaxMessenger\Bot\Models\Enums\UpdateType;

use function array_key_exists;

/**
 * Объект `Update` представляет различные события в чате или канале.
 *
 * > Чтобы получать события из группового чата или канала, назначьте бота администратором
 * > и дайте права на чтение всех сообщений.
 *
 * Типы событий:
 * - bot_added — Бот добавлен в чат или канал.
 * - bot_started — Пользователь впервые начал общение с ботом или возобновил после остановки — нажал соответствующую
 *   кнопку в настройках бота в МАКС.
 * - bot_stopped — Пользователь остановил бота – выбрал соответствующее действие в настройках бота в МАКС
 * - bot_removed — Бот удалён из чата или канала.
 * - chat_title_changed — Пользователь изменил название чата или канала.
 * - dialog_cleared — Пользователь очистил историю диалога с ботом.
 * - dialog_muted — Пользователь отключил уведомления в диалоге с ботом.
 * - dialog_unmuted — Пользователь включил уведомления в диалоге с ботом.
 * - dialog_removed — Пользователь удалил диалог с ботом.
 * - message_callback — Пользователь нажал на кнопку в чате или канале.
 * - message_created — Пользователь отправил новое сообщение или опубликовал пост.
 * - message_edited — Пользователь отредактировал сообщение в чате или канале.
 * - message_removed — Пользователь удалил сообщение из чата или канала.
 * - user_added — В чат или канал добавлен или перешёл по ссылке новый пользователь.
 * - user_removed — Пользователь удалён или покинул чат или канал.
 *
 * @link https://dev.max.ru/docs-api/objects/Update
 */
class Update extends BaseResponseModel
{
    /**
     * @var array{
     *     update_type: non-empty-string,
     *     timestamp: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return DateTimeImmutable Время, когда произошло событие.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['timestamp']);
    }

    /**
     * @return int Время, когда произошло событие (Unix-время в миллисекундах).
     */
    public function getTimestampRaw(): int
    {
        return $this->data['timestamp'];
    }

    /**
     * @return UpdateType|null Тип события.
     */
    public function getUpdateType(): ?UpdateType
    {
        return UpdateType::tryFrom($this->data['update_type']);
    }

    /**
     * @return non-empty-string Тип события (minLength: 1).
     */
    public function getUpdateTypeRaw(): string
    {
        return $this->data['update_type'];
    }

    /**
     * Creates an objects using a map.
     */
    public static function newFromData(array $data): static
    {
        if (!array_key_exists('update_type', $data) || !array_key_exists('timestamp', $data)) {
            throw new RequiredFieldException(array_key_exists('update_type', $data) ? 'timestamp' : 'update_type"');
        }

        if (static::class !== self::class) {
            /** @psalm-var static Psalm bug */
            return parent::newFromData($data);
        }

        /** @var array<string, class-string<self>> $classList */
        $classList = [
            'bot_added' => BotAddedToChatUpdate::class,
            'bot_removed' => BotRemovedFromChatUpdate::class,
            'bot_started' => BotStartedUpdate::class,
            'bot_stopped' => BotStoppedUpdate::class,
            'chat_title_changed' => ChatTitleChangedUpdate::class,
            'dialog_cleared' => DialogClearedUpdate::class,
            'dialog_muted' => DialogMutedUpdate::class,
            'dialog_removed' => DialogRemovedUpdate::class,
            'dialog_unmuted' => DialogUnmutedUpdate::class,
            'message_callback' => MessageCallbackUpdate::class,
            'message_created' => MessageCreatedUpdate::class,
            'message_edited' => MessageEditedUpdate::class,
            'message_removed' => MessageRemovedUpdate::class,
            'user_added' => UserAddedToChatUpdate::class,
            'user_removed' => UserRemovedFromChatUpdate::class,
        ];
        /** @psalm-suppress MixedArrayOffset Psalm bug */
        $className = $classList[$data['update_type']] ?? null;

        /** @psalm-var static Psalm bug */
        return $className !== null
            ? $className::newFromData($data)
            : parent::newFromData($data);
    }
}
