<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxBot\Event\SenderUnknownException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\NewMessageLink;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\User;

use function is_string;

/**
 * Методы событий, связанных с сообщениями и постами.
 *
 * Предоставляет доступ к сообщению или посту события, его отправителю и чату или каналу,
 * а также позволяет ответить, переслать и удалить сообщение или пост.
 *
 * @psalm-require-extends BaseEvent
 * @property-read MaxApiClient $apiClient
 */
trait MessageEventTrait
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;

    /**
     * Удалить сообщение или пост.
     *
     * Для удаления чужих сообщений в групповом чате требуется право {@see ChatAdminPermission::Write}.
     * Для удаления постов в канале требуется право {@see ChatAdminPermission::Delete}.
     * Нельзя удалять сообщения пользователя, отправленные в диалог.
     *
     * @throws ForbiddenException Если у бота нет прав на удаление сообщения или поста.
     * @see MaxApiClient::deleteMessage()
     */
    public function deleteMessage(): void
    {
        $this->apiClient->deleteMessage($this->getMessage());
    }

    /**
     * Переслать сообщение или пост в чат.
     *
     * Сообщение из диалога или группового чата нельзя переслать в канал.
     *
     * @param int $chatId Идентификатор чата.
     * @throws NotFoundException Если сообщение или пост удалены к моменту пересылки.
     * @see MaxApiClient::sendMessageToChat()
     */
    public function forwardToChat(int $chatId): SendMessageResult
    {
        $link = NewMessageLink::newFromMessage($this->getMessage(), MessageLinkType::Forward);
        $forwardMessage = new NewMessageBody(link: $link);

        return $this->apiClient->sendMessageToChat($chatId, $forwardMessage);
    }

    /**
     * Переслать сообщение или пост в диалог с пользователем.
     *
     * Чтобы бот мог отправить сообщение пользователю, пользователь должен запустить бота.
     *
     * @param int $userId Идентификатор пользователя.
     * @throws NotFoundException Если сообщение или пост удалены к моменту пересылки.
     * @see MaxApiClient::sendMessageToUser()
     */
    public function forwardToUser(int $userId): SendMessageResult
    {
        $link = NewMessageLink::newFromMessage($this->getMessage(), MessageLinkType::Forward);
        $forwardMessage = new NewMessageBody(link: $link);

        return $this->apiClient->sendMessageToUser($userId, $forwardMessage);
    }

    /**
     * @return int Идентификатор диалога, группового чата или канала с сообщением или постом.
     */
    public function getChatId(): int
    {
        return $this->getMessage()->getRecipient()->getChatId();
    }

    /**
     * @return Message Сообщение или пост события.
     */
    abstract public function getMessage(): Message;

    /**
     * @return User|null Пользователь или бот, отправивший сообщение или опубликовавший пост.
     *     Может быть `null`, если пост опубликован от имени канала.
     */
    public function getUser(): ?User
    {
        return $this->getMessage()->getSender();
    }

    /**
     * @return int|null Идентификатор пользователя или бота, отправившего сообщение или опубликовавшего пост.
     *     Может быть `null`, если пост опубликован от имени канала.
     */
    public function getUserId(): ?int
    {
        return $this->getUser()?->getUserId();
    }

    /**
     * @return bool `true`, если событие относится к посту в канале.
     */
    public function isChannel(): bool
    {
        return $this->getMessage()->getRecipient()->getChatType() === ChatType::Channel;
    }

    /**
     * @return bool `true`, если событие относится к сообщению в групповом чате.
     */
    public function isChat(): bool
    {
        return $this->getMessage()->getRecipient()->getChatType() === ChatType::Chat;
    }

    /**
     * @return bool `true`, если событие относится к сообщению в диалоге.
     */
    public function isDialog(): bool
    {
        return $this->getMessage()->getRecipient()->getChatType() === ChatType::Dialog;
    }

    /**
     * @return bool `true`, если тип получателя сообщения или поста не поддерживается:
     *     {@see isChannel()}, {@see isChat()} и {@see isDialog()} возвращают `false`.
     */
    public function isUnsupported(): bool
    {
        return $this->getMessage()->getRecipient()->getChatType() === null;
    }

    /**
     * Отправить сообщение или пост в исходный диалог, групповой чат или канал.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения или поста.
     * @param bool $asReply Требуется ли процитировать исходное сообщение или пост.
     * @param bool $disableLinkPreview Если `true`, сервер не создаст превью ссылок в тексте сообщения или поста.
     *     Параметр действует и при последующем редактировании.
     * @return SendMessageResult Информация о созданном сообщении или посте.
     * @throws NotFoundException Если цитируемое сообщение или пост удалены к моменту ответа (`$asReply = true`).
     */
    public function reply(
        NewMessageBody|string $message,
        bool $asReply = false,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        if ($asReply) {
            if (is_string($message)) {
                $message = new NewMessageBody($message);
            }
            $message->setReplyLink($this->getMessage());
        }

        return $this->sendMessageToChat($message, $disableLinkPreview);
    }

    /**
     * Ответить на сообщение или пост лично в диалоге с пользователем.
     *
     * Чтобы бот мог отправить сообщение пользователю, пользователь должен запустить бота.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $forwardOrigMessage Требуется ли переслать исходное сообщение или пост.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @throws NotFoundException Если пересылаемое сообщение или пост удалены до запроса (`$forwardOrigMessage = true`).
     * @throws SenderUnknownException Если отправитель сообщения или поста скрыт.
     */
    public function replyToUser(
        NewMessageBody|string $message,
        bool $forwardOrigMessage = false,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        if ($forwardOrigMessage) {
            $link = NewMessageLink::newFromMessage($this->getMessage(), MessageLinkType::Forward);

            $this->sendMessageToUser(new NewMessageBody(link: $link));
        }

        return $this->sendMessageToUser($message, $disableLinkPreview);
    }

    /**
     * @return User Отправитель сообщения или поста.
     * @throws SenderUnknownException Если пост опубликован от имени канала.
     */
    public function requireUser(): User
    {
        /**
         * @psalm-suppress TypeDoesNotContainNull, RedundantCondition В {@see MessageCallbackEvent}
         *     отправитель известен всегда.
         */
        return $this->getUser() ?? throw new SenderUnknownException();
    }

    /**
     * @return int Идентификатор отправителя сообщения или поста.
     * @throws SenderUnknownException Если пост опубликован от имени канала.
     */
    public function requireUserId(): int
    {
        /**
         * @psalm-suppress TypeDoesNotContainNull, RedundantCondition В {@see MessageCallbackEvent}
         *     отправитель известен всегда.
         */
        return $this->getUserId() ?? throw new SenderUnknownException();
    }
}
