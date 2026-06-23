<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Request\CallbackAnswer;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\Callback;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageCallbackUpdate;
use MaxMessenger\Bot\Model\Response\User;

use function is_string;

/**
 * @property-read MessageCallbackUpdate $update
 */
final class MessageCallbackEvent extends BaseEvent
{
    use MessageEventTrait;

    /**
     * @param NewMessageBody|non-empty-string $message
     * @param non-empty-string|null $notification
     */
    public function answer(NewMessageBody|string $message, ?string $notification = null): void
    {
        if (is_string($message)) {
            $message = new NewMessageBody($message);
        }

        $answer = new CallbackAnswer($message, $notification);

        $this->apiClient->answerOnCallback($this->getCallback(), $answer);
    }

    /**
     * @param non-empty-string $notification
     */
    public function answerNotification(string $notification): void
    {
        $answer = new CallbackAnswer(null, $notification);

        $this->apiClient->answerOnCallback($this->getCallback(), $answer);
    }

    /**
     * @return Callback Объект, отправленный боту, когда пользователь нажал кнопку.
     */
    public function getCallback(): Callback
    {
        return $this->update->getCallback();
    }

    /**
     * @return Message Изначальное сообщение, содержащее встроенную клавиатуру.
     */
    public function getMessage(): Message
    {
        return $this->update->getMessage();
    }

    /**
     * @return User Пользователь, нажавший на кнопку.
     */
    public function getUser(): User
    {
        return $this->getCallback()->getUser();
    }

    /**
     * @return int ID пользователя, нажавшего на кнопку.
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
