<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;

/**
 * Вы получите этот update, когда пользователь заглушит диалог с ботом.
 */
class DialogMutedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user: array,
     *     muted_until: int,
     *     user_locale?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int ID чата, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return DateTimeImmutable Время в формате Unix, до наступления которого диалог был отключён.
     */
    public function getMutedUntil(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['muted_until']);
    }

    /**
     * @return int Время в формате Unix, до наступления которого диалог был отключён (Unix-time).
     */
    public function getMutedUntilRaw(): int
    {
        return $this->data['muted_until'];
    }

    /**
     * @return User Пользователь, который отключил уведомления.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47 (minLength: 1).
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
