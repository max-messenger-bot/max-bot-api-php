<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxBot\Event\SenderUnknownException;
use MaxMessenger\Bot\Exception\MaxBot\Event\UserMissingException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\SendMessageResult;

/**
 * Метод отправки сообщения в диалог с пользователем, которого касается событие.
 *
 * @psalm-require-extends BaseEvent
 * @property-read MaxApiClient $apiClient
 */
trait SendMessageToUserTrait
{
    /**
     * @return int Идентификатор пользователя события.
     * @throws UserMissingException Если у события нет пользователя.
     * @throws SenderUnknownException Если отправитель сообщения или комментария скрыт.
     */
    abstract public function requireUserId(): int;

    /**
     * Отправить сообщение лично в диалоге с пользователем.
     *
     * Чтобы бот мог отправить сообщение пользователю, пользователь должен запустить бота.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @throws UserMissingException Если у события нет пользователя.
     * @throws SenderUnknownException Если в событии сообщения или комментария отправитель скрыт.
     * @see MaxApiClient::sendMessageToUser()
     */
    public function sendMessageToUser(
        NewMessageBody|string $message,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->apiClient->sendMessageToUser($this->requireUserId(), $message, $disableLinkPreview);
    }
}
