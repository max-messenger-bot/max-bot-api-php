<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use DateTimeImmutable;
use MaxMessenger\Bot\Model\Response\DialogMutedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * Событие отключения уведомлений в диалоге, чате или канале.
 *
 * @property-read DialogMutedUpdate $update
 * @psalm-suppress DeprecatedTrait {@see UserEventTrait} подключён для совместимости.
 */
final class DialogMutedEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;
    use UserEventTrait;

    /**
     * @return int ID диалога, чата или канала, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return DateTimeImmutable Время, до наступления которого уведомления в диалоге, чате или канале были отключены.
     */
    public function getMutedUntil(): DateTimeImmutable
    {
        return $this->update->getMutedUntil();
    }

    /**
     * @return non-negative-int Время, до наступления которого уведомления в диалоге, чате или канале
     *     были отключены (Unix-время в миллисекундах).
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
