<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use DateTimeImmutable;
use MaxMessenger\Bot\Model\Response\DialogMutedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * Событие отключения уведомлений в диалоге.
 *
 * @property-read DialogMutedUpdate $update
 */
final class DialogMutedEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;

    /**
     * @return int Идентификатор диалога, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return DateTimeImmutable Время, до наступления которого уведомления в диалоге были отключены.
     */
    public function getMutedUntil(): DateTimeImmutable
    {
        return $this->update->getMutedUntil();
    }

    /**
     * @return non-negative-int Время, до наступления которого уведомления в диалоге были отключены
     *     (Unix-время в миллисекундах).
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
     * @return int Идентификатор пользователя, который отключил уведомления.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }
}
