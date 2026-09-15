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
    abstract public function requireUserId(): int;

    /**
     * Отправить сообщение лично в диалоге с пользователем.
     *
     * - Если у события нет пользователя, {@see requireUserId()} выбросит ошибку: {@see UserMissingException}
     *   или, в событиях сообщений и комментариев, {@see SenderUnknownException}.
     * - Чтобы бот мог отправить сообщение пользователю, пользователь должен запустить бота.
     *   Иначе запрос завершится ошибкой.
     * - Можно отправлять не более двух сообщений в секунду в один диалог. При превышении этого лимита
     *   сообщения следует ставить в очередь или делать задержку перед отправкой.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @see MaxApiClient::sendMessageToUser()
     */
    public function sendMessageToUser(
        NewMessageBody|string $message,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->apiClient->sendMessageToUser($this->requireUserId(), $message, $disableLinkPreview);
    }

    /**
     * Отправить сообщение лично в диалоге с пользователем.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @deprecated Метод будет удалён в следующих версиях. Используйте {@see sendMessageToUser()}.
     */
    public function sendToUser(NewMessageBody|string $message, bool $disableLinkPreview = false): SendMessageResult
    {
        return $this->sendMessageToUser($message, $disableLinkPreview);
    }
}
