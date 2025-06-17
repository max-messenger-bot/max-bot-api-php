<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;
use MaxMessenger\Bot\Models\Responses\SendMessageResult;
use MaxMessenger\Bot\Models\Responses\User;

/**
 * @psalm-require-extends BaseEvent
 * @property-read MaxApiClient $apiClient
 */
trait UserEventTrait
{
    abstract public function getChatId(): int;

    abstract public function getUser(): User;

    /**
     * Отправить сообщение лично в диалоге с пользователем.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendToChat(NewMessageBody|string $message, bool $disableLinkPreview = false): SendMessageResult
    {
        return $this->apiClient->sendMessageToChat($this->getChatId(), $message, $disableLinkPreview);
    }

    /**
     * Отправить сообщение в чат события.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendToUser(NewMessageBody|string $message, bool $disableLinkPreview = false): SendMessageResult
    {
        return $this->apiClient->sendMessageToUser($this->getUser()->getUserId(), $message, $disableLinkPreview);
    }
}
