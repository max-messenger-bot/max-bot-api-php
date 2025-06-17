<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;

/**
 * Объект содержит общую информацию о пользователе или боте без аватара.
 *
 * Варианты наследования:
 * - {@see UserWithPhoto} - Объект с общей информацией о пользователе или боте,
 *   дополнительно содержит URL аватара и описание.
 * - {@see BotInfo} - Объект включает общую информацию о боте, URL аватара и описание.
 *   Дополнительно содержит список команд, поддерживаемых ботом.
 * - {@see ChatMember} - Объект включает общую информацию о пользователе или боте, URL аватара и описание
 *   при его наличии. Дополнительно содержит данные для пользователей-участников чата.
 *
 * @link https://dev.max.ru/docs-api/objects/User
 * @api
 */
class User extends BaseResponseModel
{
    /**
     * @var array{
     *     user_id: int,
     *     first_name: string,
     *     last_name?: string|null,
     *     username?: string|null,
     *     is_bot: bool,
     *     last_activity_time?: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string Отображаемое имя пользователя или бота.
     * @api
     */
    public function getFirstName(): string
    {
        return $this->data['first_name'];
    }

    /**
     * @return DateTimeImmutable|null Время последней активности пользователя или бота в MAX.
     *     Если пользователь отключил в настройках профиля мессенджера MAX возможность видеть,
     *     что он в сети онлайн, поле может не возвращаться.
     * @api
     */
    public function getLastActivityTime(): ?DateTimeImmutable
    {
        return static::makeNullableDateTime($this->data['last_activity_time'] ?? null);
    }

    /**
     * @return int|null Время последней активности пользователя или бота в MAX (Unix-время в миллисекундах).
     *     Если пользователь отключил в настройках профиля мессенджера MAX возможность видеть,
     *     что он в сети онлайн, поле может не возвращаться.
     * @api
     */
    public function getLastActivityTimeRaw(): ?int
    {
        return $this->data['last_activity_time'] ?? null;
    }

    /**
     * @return string|null Отображаемая фамилия пользователя. Для ботов это поле не возвращается.
     * @api
     */
    public function getLastName(): ?string
    {
        return $this->data['last_name'] ?? null;
    }

    /**
     * @return int Идентификатор пользователя или бота.
     * @api
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }

    /**
     * @return string|null Никнейм бота или уникальное публичное имя пользователя.
     *     В случае с пользователем может быть `null`, если тот недоступен или имя не задано.
     * @api
     */
    public function getUsername(): ?string
    {
        return $this->data['username'] ?? null;
    }

    /**
     * @return bool `true`, если это бот.
     * @api
     */
    public function isBot(): bool
    {
        return $this->data['is_bot'];
    }
}
