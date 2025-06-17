<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Models\Requests\CallbackAnswer;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;
use MaxMessenger\Bot\Models\Responses\Callback;
use MaxMessenger\Bot\Models\Responses\Message;
use MaxMessenger\Bot\Models\Responses\MessageCallbackUpdate;
use MaxMessenger\Bot\Models\Responses\User;

use function is_string;

/**
 * @property-read MessageCallbackUpdate $update
 */
final class MessageCallbackEvent extends BaseEvent
{
    use UserEventTrait;

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

        $this->apiClient->answerOnCallback($this->getCallback()->getCallbackId(), $answer);
    }

    /**
     * @param non-empty-string $notification
     */
    public function answerNotification(string $notification): void
    {
        $answer = new CallbackAnswer(null, $notification);

        $this->apiClient->answerOnCallback($this->getCallback()->getCallbackId(), $answer);
    }

    /**
     * Удалить сообщение.
     *
     * @param non-empty-string|null $mid
     */
    public function deleteMessage(string $mid = null): void
    {
        $this->apiClient->deleteMessage($mid ?? $this->getMessage()->getBody()->getMid());
    }

    /**
     * @return Callback Объект, отправленный боту, когда пользователь нажал кнопку.
     */
    public function getCallback(): Callback
    {
        return $this->update->getCallback();
    }

    public function getChatId(): int
    {
        return $this->getMessage()->getRecipient()->getChatId();
    }

    /**
     * @return Message Изначальное сообщение, содержащее встроенную клавиатуру.
     */
    public function getMessage(): Message
    {
        return $this->update->getMessage();
    }

    public function getUser(): User
    {
        return $this->getCallback()->getUser();
    }

    /**
     * @return string|null Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }
}
