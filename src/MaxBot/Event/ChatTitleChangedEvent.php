<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\ChatTitleChangedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * Событие изменения названия чата или канала.
 *
 * На свои действия бот события не получает.
 *
 * @property-read ChatTitleChangedUpdate $update
 * @psalm-suppress DeprecatedTrait {@see UserEventTrait} подключён для совместимости.
 */
final class ChatTitleChangedEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;
    use UserEventTrait;

    /**
     * @return int ID чата, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return non-empty-string Новое название.
     */
    public function getTitle(): string
    {
        return $this->update->getTitle();
    }

    /**
     * @return User Пользователь, который изменил название.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int ID пользователя, который изменил название.
     */
    public function getUserId(): int
    {
        return $this->getUser()->getUserId();
    }
}
