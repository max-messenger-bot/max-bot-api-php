<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxBot\Event\MessageMissingException;
use MaxMessenger\Bot\Exception\MaxBot\Event\SenderUnknownException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\NewMessageLink;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\User;

use function is_string;

trait MessageEventTrait
{
    /**
     * Удалить сообщение.
     *
     * - Вы получите ошибку {@see ForbiddenException}, если попытаетесь удалить чужое сообщения
     *   без прав на удаление сообщений.
     * - Нельзя удалять сообщения пользователя отправленные в диалог.
     */
    public function deleteMessage(): void
    {
        $this->apiClient->deleteMessage($this->requireMessage()->getBody()->getMid());
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
        $link = NewMessageLink::newFromMessage($this->requireMessage(), MessageLinkType::Forward);
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
        $link = NewMessageLink::newFromMessage($this->requireMessage(), MessageLinkType::Forward);
        $forwardMessage = new NewMessageBody(link: $link);

        return $this->apiClient->sendMessageToUser($userId, $forwardMessage);
    }

    public function getChatId(): ?int
    {
        /** @psalm-suppress TypeDoesNotContainNull, RedundantCondition Psalm bug */
        return $this->getMessage()?->getRecipient()->getChatId();
    }

    abstract public function getMessage(): ?Message;

    /**
     * @return User|null Пользователь, отправивший сообщение.
     *     Может быть `null`, если сообщение было опубликовано от имени канала.
     */
    public function getUser(): ?User
    {
        /** @psalm-suppress TypeDoesNotContainNull, RedundantCondition Psalm bug */
        return $this->getMessage()?->getSender();
    }

    /**
     * @return int|null ID пользователя, отправившего сообщение.
     *     Может быть `null`, если сообщение было опубликовано от имени канала.
     */
    public function getUserId(): ?int
    {
        return $this->getUser()?->getUserId();
    }

    /**
     * @return bool `true`, если сообщение было отправлено в канал.
     */
    public function isChannel(): bool
    {
        /** @psalm-suppress TypeDoesNotContainNull, RedundantCondition Psalm bug */
        return $this->getMessage()?->getRecipient()->getChatType() === ChatType::Channel;
    }

    /**
     * @return bool `true`, если сообщение было отправлено в группу.
     */
    public function isChat(): bool
    {
        /** @psalm-suppress TypeDoesNotContainNull, RedundantCondition Psalm bug */
        return $this->getMessage()?->getRecipient()->getChatType() === ChatType::Chat;
    }

    /**
     * @return bool `true`, если сообщение было отправлено в диалог.
     */
    public function isDialog(): bool
    {
        /** @psalm-suppress TypeDoesNotContainNull, RedundantCondition Psalm bug */
        return $this->getMessage()?->getRecipient()->getChatType() === ChatType::Dialog;
    }

    /**
     * Ответить на сообщение в чате сообщения.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если сообщение на момент цитирования будет удалено.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $asReply Требуется ли ответить с цитированием сообщения
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function reply(
        NewMessageBody|string $message,
        bool $asReply = false,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        $origMessage = $this->requireMessage();
        $chatId = $origMessage->getRecipient()->getChatId();

        if ($asReply) {
            if (is_string($message)) {
                $message = new NewMessageBody($message);
            }
            $message->setReplyLink($origMessage);
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
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function replyToUser(
        NewMessageBody|string $message,
        bool $forwardOrigMessage = false,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        $origMessage = $this->requireMessage();
        $sender = $origMessage->getSender() ?? throw new SenderUnknownException();

        if ($forwardOrigMessage) {
            $link = NewMessageLink::newFromMessage($origMessage, MessageLinkType::Forward);
            $forwardMessage = new NewMessageBody(link: $link);

            $this->apiClient->sendMessageToUser($sender->getUserId(), $forwardMessage);
        }

        return $this->apiClient->sendMessageToUser($sender->getUserId(), $message, $disableLinkPreview);
    }

    private function requireMessage(): Message
    {
        /** @psalm-suppress TypeDoesNotContainNull, RedundantCondition Psalm bug */
        return $this->getMessage() ?? throw new MessageMissingException();
    }
}
