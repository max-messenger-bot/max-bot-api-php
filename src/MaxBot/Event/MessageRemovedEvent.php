<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\MessageRemovedUpdate;
use MaxMessenger\Bot\Model\Response\SendMessageResult;

/**
 * Событие удаления сообщения.
 *
 * На свои действия бот события не получает.
 *
 * @property-read MessageRemovedUpdate $update
 * @psalm-suppress DeprecatedTrait {@see UserEventTrait} подключён для совместимости.
 */
final class MessageRemovedEvent extends BaseEvent
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;
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

    public function getUser(): null
    {
        return null;
    }

    /**
     * @return int ID пользователя, удалившего сообщение.
     */
    public function getUserId(): int
    {
        return $this->update->getUserId();
    }

    /**
     * Отправить сообщение пользователю, удалившему сообщение.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @deprecated Используйте {@see SendMessageToUserTrait::sendMessageToUser()} — метод дублирует его поведение.
     */
    public function sendMessage(NewMessageBody|string $message, bool $disableLinkPreview = false): SendMessageResult
    {
        return $this->sendMessageToUser($message, $disableLinkPreview);
    }
}
