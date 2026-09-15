<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use DateTimeImmutable;

/**
 * Вы получите это событие, как только пользователь отключит уведомления о новых сообщениях в диалоге, чате или канале.
 */
class DialogMutedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user: array,
     *     muted_until: non-negative-int,
     *     user_locale?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int ID диалога, чата или канала, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return DateTimeImmutable Время, до наступления которого уведомления в диалоге, чате или канале были отключены.
     */
    public function getMutedUntil(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['muted_until']);
    }

    /**
     * @return non-negative-int Время, до наступления которого уведомления в диалоге, чате или канале
     *     были отключены (Unix-время в миллисекундах).
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
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
