<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use DateTimeImmutable;
use MaxMessenger\Bot\Model\Response\DialogMutedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * @property-read DialogMutedUpdate $update
 */
final class DialogMutedEvent extends BaseEvent
{
    use UserEventTrait;

    /**
     * @return int ID чата, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return DateTimeImmutable Время, до наступления которого диалог был отключён.
     */
    public function getMutedUntil(): DateTimeImmutable
    {
        return $this->update->getMutedUntil();
    }

    /**
     * @return int Время, до наступления которого диалог был отключён (Unix-time).
     */
    public function getMutedUntilRaw(): int
    {
        return $this->update->getMutedUntilRaw();
    }

    /**
     * @return User Пользователь, который отключил уведомления.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int ID пользователя, который отключил уведомления.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }

    /**
     * @return string|null Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }
}
