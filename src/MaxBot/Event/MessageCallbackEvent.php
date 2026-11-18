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
 * Событие нажатия callback-кнопки в диалоге, чате или канале.
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
     * В отличие от обычного редактирования сообщения, при ответе на callback сервер автоматически удаляет
     * вложения, включая клавиатуру, если они не переданы в обновлённом сообщении.
     *
     * @param NewMessageBody|non-empty-string $message Тело обновлённого сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте
     *     сообщения или поста.
     * @see MaxApiClient::answerOnCallback()
     */
    public function answer(NewMessageBody|string $message, bool $disableLinkPreview = false): void
    {
        if (is_string($message)) {
            $message = new NewMessageBody($message);
        }

        $this->apiClient->answerOnCallback($this->getCallback(), new CallbackAnswer($message), $disableLinkPreview);
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
     * @return int Идентификатор пользователя, нажавшего на кнопку.
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
