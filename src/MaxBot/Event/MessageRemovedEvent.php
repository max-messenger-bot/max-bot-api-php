<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\MessageRemovedUpdate;
use MaxMessenger\Bot\Model\Response\SendMessageResult;

/**
 * @property-read MessageRemovedUpdate $update
 */
final class MessageRemovedEvent extends BaseEvent
{
    use UserEventTrait;

    /**
     * @return int ID чата, где сообщение было удалено.
     */
    public function getChatId(): int
    {
        return $this->update->getChatId();
    }

    /**
     * @return non-empty-string ID удалённого сообщения.
     */
    public function getMessageId(): string
    {
        return $this->update->getMessageId();
    }

    /**
     * @return int ID пользователя, удалившего сообщение.
     */
    public function getUserId(): int
    {
        return $this->update->getUserId();
    }

    /**
     * Отправить сообщение пользователю удалившему сообщение.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendMessage(NewMessageBody|string $message, bool $disableLinkPreview = false): SendMessageResult
    {
        return $this->apiClient->sendMessageToUser($this->getUserId(), $message, $disableLinkPreview);
    }
}
