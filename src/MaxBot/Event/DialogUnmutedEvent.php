<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\DialogUnmutedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * Событие включения уведомлений в диалоге, чате или канале.
 *
 * @property-read DialogUnmutedUpdate $update
 * @psalm-suppress DeprecatedTrait {@see UserEventTrait} подключён для совместимости.
 */
final class DialogUnmutedEvent extends BaseEvent
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
     * @return User Пользователь, который включил уведомления.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int ID пользователя, который включил уведомления.
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
