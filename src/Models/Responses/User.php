<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;
use MaxMessenger\Bot\MaxApiClient;

use function sprintf;

/**
 * Объект содержит общую информацию о пользователе или боте без аватара.
 *
 * Варианты наследования:
 * - UserWithPhoto — объект с общей информацией о пользователе или боте, дополнительно содержит URL аватара и описание.
 * - BotInfo — объект включает общую информацию о боте, URL аватара и описание. Дополнительно содержит список команд,
 *   поддерживаемых ботом. Возвращается только при вызове метода {@see MaxApiClient::getMyInfo()}
 * - ChatMember — объект включает общую информацию о пользователе или боте, URL аватара и описание при его наличии.
 *   Дополнительно содержит данные для пользователей-участников чата.
 *   Возвращается только при вызове некоторых методов группы /chats, например {@see MaxApiClient::getMembers()}
 *
 * @link https://dev.max.ru/docs-api/objects/User
 */
class User extends BaseResponseModel
{
    /**
     * @var array{
     *     user_id: int,
     *     first_name: non-empty-string,
     *     last_name?: non-empty-string,
     *     username?: non-empty-string,
     *     is_bot: bool,
     *     last_activity_time?: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Отображаемое имя пользователя или бота (minLength: 1).
     */
    public function getFirstName(): string
    {
        return $this->data['first_name'];
    }

    /**
     * @return non-empty-string Полное имя пользователя, включая фамилию (minLength: 1).
     */
    public function getFullName(): string
    {
        $firstName = $this->getFirstName();
        $lastName = $this->getLastName();

        /** @psalm-suppress RedundantCondition */
        return $lastName !== null && $lastName !== ''
            ? sprintf('%s %s', $firstName, $lastName)
            : $firstName;
    }

    /**
     * @return DateTimeImmutable|null Время последней активности пользователя или бота в MAX (Unix-время
     *     в миллисекундах). Если пользователь отключил в настройках профиля мессенджера MAX возможность видеть,
     *     что он в сети онлайн, поле может не возвращаться.
     */
    public function getLastActivityTime(): ?DateTimeImmutable
    {
        return static::makeNullableDateTime($this->data['last_activity_time'] ?? null);
    }

    /**
     * @return int|null Время последней активности пользователя или бота в MAX (Unix-время в миллисекундах).
     *     Если пользователь отключил в настройках профиля мессенджера MAX возможность видеть,
     *     что он в сети онлайн, поле может не возвращаться.
     */
    public function getLastActivityTimeRaw(): ?int
    {
        return $this->data['last_activity_time'] ?? null;
    }

    /**
     * @return non-empty-string|null Отображаемая фамилия пользователя (minLength: 1).
     *     Для ботов это поле не возвращается.
     */
    public function getLastName(): ?string
    {
        return $this->data['last_name'] ?? null;
    }

    /**
     * @return int Идентификатор пользователя или бота.
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }

    /**
     * @return non-empty-string|null Никнейм бота или уникальное публичное имя пользователя (minLength: 1).
     *     В случае с пользователем может быть `null`, если тот недоступен или имя не задано.
     */
    public function getUsername(): ?string
    {
        return $this->data['username'] ?? null;
    }

    /**
     * @return bool `true`, если это бот.
     */
    public function isBot(): bool
    {
        return $this->data['is_bot'];
    }
}
