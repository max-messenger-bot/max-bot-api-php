<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;
use MaxMessenger\Bot\Exceptions\Validation\RequiredFieldException;
use MaxMessenger\Bot\Models\Enums\UpdateType;

use function array_key_exists;

/**
 * Объект `Update` представляет различные типы событий, произошедших в чате.
 *
 * > Чтобы получать события из группового чата или канала, назначьте бота администратором
 * > и дайте права на чтение всех сообщений.
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
     * @return DateTimeImmutable Unix-время, когда произошло событие.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['timestamp']);
    }

    /**
     * @return int Unix-время, когда произошло событие (Unix timestamp в миллисекундах).
     */
    public function getTimestampRaw(): int
    {
        return $this->data['timestamp'];
    }

    /**
     * @return UpdateType|null Тип обновления.
     */
    public function getUpdateType(): ?UpdateType
    {
        return UpdateType::tryFrom($this->data['update_type']);
    }

    /**
     * @return non-empty-string Тип обновления (minLength: 1).
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
