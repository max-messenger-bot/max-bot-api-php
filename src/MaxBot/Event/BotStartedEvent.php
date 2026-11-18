<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\BotStartedUpdate;
use MaxMessenger\Bot\Model\Response\User;

/**
 * Событие запуска бота пользователем.
 *
 * Приходит, когда пользователь впервые начал общение с ботом или возобновил его после остановки —
 * например нажал кнопку «Начать» в интерфейсе МАКС.
 *
 * @property-read BotStartedUpdate $update
 */
final class BotStartedEvent extends BaseEvent
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
     * @return non-empty-string|null Дополнительные данные из диплинков, переданные при запуске бота (maxLength: 128).
     */
    public function getPayload(): ?string
    {
        return $this->update->getPayload();
    }

    /**
     * @return User Пользователь, который нажал кнопку 'Начать'.
     */
    public function getUser(): User
    {
        return $this->update->getUser();
    }

    /**
     * @return int Идентификатор пользователя, который нажал кнопку 'Начать'.
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
