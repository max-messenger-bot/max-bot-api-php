<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Events;

use MaxMessenger\Bot\Exceptions\MaxBot\Events\SenderUnknownException;
use MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Enums\ChatType;
use MaxMessenger\Bot\Models\Enums\MessageLinkType;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;
use MaxMessenger\Bot\Models\Requests\NewMessageLink;
use MaxMessenger\Bot\Models\Responses\Message;
use MaxMessenger\Bot\Models\Responses\SendMessageResult;

use function is_string;

/**
 * @psalm-require-extends BaseEvent
 * @property-read MaxApiClient $apiClient
 */
trait MessageEventTrait
{
    /**
     * Удалить сообщение.
     *
     * - Вы получите ошибку {@see ForbiddenException}, если попытаетесь удалить чужое сообщения
     *   без прав на удаление сообщений.
     *
     * @param non-empty-string|null $mid
     */
    public function deleteMessage(string $mid = null): void
    {
        $this->apiClient->deleteMessage($mid ?? $this->getMessage()->getBody()->getMid());
    }

    /**
     * Переслать сообщение в чат.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если сообщение на момент пересылки будет удалено.
     *
     * @param int $chatId ID чата.
     */
    public function forwardToChat(int $chatId): SendMessageResult
    {
        $origMessage = $this->getMessage();

        $link = NewMessageLink::newFromMessage($origMessage, MessageLinkType::Forward);
        $forwardMessage = new NewMessageBody(link: $link);

        return $this->apiClient->sendMessageToChat($chatId, $forwardMessage);
    }

    /**
     * Переслать сообщение в диалог с пользователем.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если сообщение на момент пересылки будет удалено.
     *
     * @param int $userId ID пользователя.
     */
    public function forwardToUser(int $userId): SendMessageResult
    {
        $origMessage = $this->getMessage();

        $link = NewMessageLink::newFromMessage($origMessage, MessageLinkType::Forward);
        $forwardMessage = new NewMessageBody(link: $link);

        return $this->apiClient->sendMessageToUser($userId, $forwardMessage);
    }

    abstract public function getMessage(): Message;

    /**
     * @return bool `true`, если сообщение было отправлено в канал.
     */
    public function isChannel(): bool
    {
        return $this->getMessage()->getRecipient()->getChatType() === ChatType::Channel;
    }

    /**
     * @return bool `true`, если сообщение было отправлено в группу.
     */
    public function isChat(): bool
    {
        return $this->getMessage()->getRecipient()->getChatType() === ChatType::Chat;
    }

    /**
     * @return bool `true`, если сообщение было отправлено в диалог.
     */
    public function isDialog(): bool
    {
        return $this->getMessage()->getRecipient()->getChatType() === ChatType::Dialog;
    }

    /**
     * Ответить на сообщение в чате сообщения.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если сообщение на момент цитирования будет удалено.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $asReply Требуется ли ответить с цитированием сообщения
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function reply(
        NewMessageBody|string $message,
        bool $asReply = false,
        bool $disableLinkPreview = false
    ): SendMessageResult {
        $origMessage = $this->getMessage();
        $chatId = $origMessage->getRecipient()->getChatId();

        if ($asReply) {
            if (is_string($message)) {
                $message = new NewMessageBody($message);
            }
            $message->setLink(NewMessageLink::newFromMessage($origMessage));
        }

        return $this->apiClient->sendMessageToChat($chatId, $message, $disableLinkPreview);
    }

    /**
     * Ответить на сообщение лично в диалоге с пользователем.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если сообщение на момент пересылки будет удалено.
     * - Вы можете получить ошибку {@see SenderUnknownException}, если отправитель сообщения скрыт.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $forwardOrigMessage Требуется ли переслать оригинальное сообщение
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function replyToUser(
        NewMessageBody|string $message,
        bool $forwardOrigMessage = false,
        bool $disableLinkPreview = false
    ): SendMessageResult {
        $origMessage = $this->getMessage();
        $sender = $origMessage->getSender() ?? throw new SenderUnknownException();

        if ($forwardOrigMessage) {
            $link = NewMessageLink::newFromMessage($origMessage, MessageLinkType::Forward);
            $forwardMessage = new NewMessageBody(link: $link);

            $this->apiClient->sendMessageToUser($sender->getUserId(), $forwardMessage);
        }

        return $this->apiClient->sendMessageToUser($sender->getUserId(), $message, $disableLinkPreview);
    }
}
