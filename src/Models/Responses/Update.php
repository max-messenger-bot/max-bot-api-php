<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;
use JsonException;
use MaxMessenger\Bot\Exceptions\RuntimeException;
use MaxMessenger\Bot\Models\Enums\UpdateType;

use function array_key_exists;
use function is_array;

/**
 * `Update` object represents different types of events that happened in chat.
 *
 * See its inheritors.
 *
 * @link https://dev.max.ru/docs-api/objects/Update
 * @api
 */
class Update extends BaseResponseModel
{
    /**
     * @var array{
     *     update_type: string,
     *     timestamp: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return DateTimeImmutable Time when event has occurred.
     * @api
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['timestamp']);
    }

    /**
     * @return int Unix-time when event has occurred (Unix timestamp in milliseconds).
     * @api
     */
    public function getTimestampRaw(): int
    {
        return $this->data['timestamp'];
    }

    /**
     * @return UpdateType|null Type of update.
     * @api
     */
    public function getUpdateType(): ?UpdateType
    {
        return UpdateType::tryFrom($this->data['update_type']);
    }

    /**
     * @return string Type of update.
     * @api
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
        if (static::class !== self::class) {
            /** @psalm-var static Psalm bug */
            return parent::newFromData($data);
        }

        /** @var array<string, class-string<self>> $classList */
        $classList = [
            'bot_added' => BotAddedUpdate::class,
            'bot_removed' => BotRemovedUpdate::class,
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
            'user_added' => UserAddedUpdate::class,
            'user_removed' => UserRemovedUpdate::class,
        ];
        /** @var array{update_type: string} $data */
        $className = $classList[$data['update_type']] ?? null;

        /** @psalm-var static Psalm bug */
        return $className !== null
            ? $className::newFromData($data)
            : parent::newFromData($data);
    }

    /**
     * Создать объект обновления из JSON-стоки.
     */
    public static function newFromJsonString(string $body): self
    {
        try {
            $data = json_decode($body, true, 16, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('The body does not contain valid JSON.', 400, $e);
        }

        if (!is_array($data)) {
            throw new RuntimeException('Data is not array.');
        }
        if (!array_key_exists('update_type', $data) || !array_key_exists('timestamp', $data)) {
            throw new RuntimeException('Data requires "update_type" and "timestamp" fields.');
        }

        return self::newFromData($data);
    }
}
