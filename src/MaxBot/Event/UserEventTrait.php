<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\SendMessageResult;

/**
 * @psalm-require-extends BaseEvent
 * @property-read MaxApiClient $apiClient
 */
trait UserEventTrait
{
    abstract public function getChatId(): int;

    abstract public function getUserId(): int;

    /**
     * Отправить сообщение в чат события.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     *
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendToChat(NewMessageBody|string $message, bool $disableLinkPreview = false): SendMessageResult
    {
        return $this->apiClient->sendMessageToChat($this->getChatId(), $message, $disableLinkPreview);
    }

    /**
     * Отправить сообщение лично в диалоге с пользователем.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendToUser(NewMessageBody|string $message, bool $disableLinkPreview = false): SendMessageResult
    {
        return $this->apiClient->sendMessageToUser($this->getUserId(), $message, $disableLinkPreview);
    }
}
