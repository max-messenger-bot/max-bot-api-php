<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use DateTimeImmutable;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\UpdateType;

use function array_diff;
use function array_key_exists;
use function array_keys;

/**
 * Объект, представляющий различные события в диалоге, групповом чате или канале.
 *
 * Типы событий и особенности их получения описаны в {@see UpdateType}.
 *
 * Согласно документации, бот не получает события на собственные действия.
 * **По результатам тестов**: в каналах бот может получать события на собственные действия.
 *
 * @link https://dev.max.ru/docs-api/objects/Update
 */
class Update extends BaseResponseModel
{
    /**
     * @var array{
     *     update_type: non-empty-string,
     *     timestamp: non-negative-int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return list<non-empty-string> Имена обязательных свойств обновления без update_type и timestamp.
     */
    public function getRequiredProperties(): array
    {
        return [];
    }

    /**
     * @return DateTimeImmutable Время, когда произошло событие.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['timestamp']);
    }

    /**
     * @return non-negative-int Время, когда произошло событие (Unix-время в миллисекундах).
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
     * @return non-empty-string Тип события. Значения описаны в {@see UpdateType}.
     */
    public function getUpdateTypeRaw(): string
    {
        return $this->data['update_type'];
    }

    /**
     * Проверяет наличие всех обязательных свойств обновления независимо от их значений.
     */
    public function isValid(): bool
    {
        return array_diff($this->getRequiredProperties(), array_keys($this->data)) === [];
    }

    /**
     * Создаёт объект обновления нужного типа по данным ответа API.
     *
     * @param array $data Данные обновления из ответа API.
     */
    public static function newFromData(array $data): static
    {
        if (!array_key_exists('update_type', $data) || !array_key_exists('timestamp', $data)) {
            throw new RequiredFieldException(array_key_exists('update_type', $data) ? 'timestamp' : 'update_type');
        }

        if (static::class !== self::class) {
            /** @psalm-var static Psalm bug */
            return parent::newFromData($data);
        }

        /** @var array<string, class-string<self>> $classList */
        $classList = [
            'bot_added' => BotAddedToChatUpdate::class,
            'bot_admin_permissions_changed' => BotAdminPermissionsChangedUpdate::class,
            'bot_removed' => BotRemovedFromChatUpdate::class,
            'bot_started' => BotStartedUpdate::class,
            'bot_stopped' => BotStoppedUpdate::class,
            'chat_title_changed' => ChatTitleChangedUpdate::class,
            'comment_created' => CommentCreatedUpdate::class,
            'comment_edited' => CommentEditedUpdate::class,
            'comment_removed' => CommentRemovedUpdate::class,
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
