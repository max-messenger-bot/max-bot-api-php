<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;

/**
 * Объект, отправленный боту, когда пользователь нажимает кнопку.
 */
class Callback extends BaseResponseModel
{
    /**
     * @var array{
     *     timestamp: int,
     *     callback_id: non-empty-string,
     *     payload: non-empty-string,
     *     user: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return non-empty-string Текущий ID клавиатуры (minLength: 1).
     */
    public function getCallbackId(): string
    {
        return $this->data['callback_id'];
    }

    /**
     * @return non-empty-string Токен кнопки (minLength: 1, maxLength: 1024).
     */
    public function getPayload(): string
    {
        return $this->data['payload'];
    }

    /**
     * @return DateTimeImmutable Unix-время, когда пользователь нажал кнопку.
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['timestamp']);
    }

    /**
     * @return int Unix-время, когда пользователь нажал кнопку (Unix-time).
     */
    public function getTimestampRaw(): int
    {
        return $this->data['timestamp'];
    }

    /**
     * @return User Пользователь, нажавший на кнопку.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }
}
