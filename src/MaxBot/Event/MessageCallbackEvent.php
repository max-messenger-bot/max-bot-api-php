<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Request\CallbackAnswer;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\Callback;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageCallbackUpdate;
use MaxMessenger\Bot\Model\Response\User;

use function is_string;

/**
 * Событие нажатия кнопки в чате или канале.
 *
 * @property-read MessageCallbackUpdate $update
 */
final class MessageCallbackEvent extends BaseEvent
{
    use MessageEventTrait;

    /**
     * Отправить ответ на нажатие кнопки.
     *
     * Ответом является обновлённое сообщение.
     *
     * - Можно отправлять не более двух ответов в секунду в один диалог, групповой чат или канал.
     *   При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед отправкой.
     *
     * @param NewMessageBody|non-empty-string $message
     * @param non-empty-string|null $notification Устарело: с 21 июля 2026 г. поле удалено из официальной
     *     схемы API — на сервер не передаётся и игнорируется.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте
     *     сообщения или поста.
     * @see MaxApiClient::answerOnCallback()
     */
    public function answer(
        NewMessageBody|string $message,
        ?string $notification = null,
        bool $disableLinkPreview = false,
    ): void {
        if (is_string($message)) {
            $message = new NewMessageBody($message);
        }

        $answer = new CallbackAnswer($message, $notification);

        $this->apiClient->answerOnCallback($this->getCallback(), $answer, $disableLinkPreview);
    }

    /**
     * @param non-empty-string $notification
     * @deprecated С 21 июля 2026 г. поле notification удалено из официальной схемы API — метод ничего не делает.
     * @psalm-suppress UnusedParam Параметр сохранён для обратной совместимости сигнатуры.
     */
    public function answerNotification(string $notification): void {}

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

    /**
     * @return bool Всегда `true`: событие всегда содержит сообщение.
     */
    public function hasMessage(): bool
    {
        return true;
    }
}
