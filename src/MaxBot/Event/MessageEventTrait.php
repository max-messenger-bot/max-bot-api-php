<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxBot\Event\SenderUnknownException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\NewMessageLink;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\User;

use function is_string;

/**
 * Методы событий, связанных с сообщениями.
 *
 * Предоставляет доступ к сообщению события, его отправителю и чату, а также действия над сообщением:
 * ответ, пересылку и удаление.
 *
 * Событие может относиться к объекту, который не поддерживается MAX API, — тогда сообщения в нём нет
 * и любой метод трейта прерывает обработку события через {@see BaseEvent::continue()}. Проверить наличие
 * сообщения заранее можно методом {@see hasMessage()}.
 *
 * @psalm-require-extends BaseEvent
 * @property-read MaxApiClient $apiClient
 */
trait MessageEventTrait
{
    use SendMessageToChatTrait;
    use SendMessageToUserTrait;

    /**
     * Удалить сообщение.
     *
     * Бот удаляет сообщения, если он администратор и имеет право на удаление: в канале и групповом
     * чате — любые сообщения, в диалоге — только отправленные самим ботом.
     *
     * - Вы получите ошибку {@see ForbiddenException}, если попытаетесь удалить чужое сообщения
     *   без прав на удаление сообщений.
     * - Нельзя удалять сообщения пользователя отправленные в диалог.
     * - Можно удалять не более двух сообщений в секунду в одном диалоге, групповом чате или канале.
     *   При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед удалением.
     *
     * @see MaxApiClient::deleteMessage()
     */
    public function deleteMessage(): void
    {
        $this->apiClient->deleteMessage($this->getMessage());
    }

    /**
     * Переслать сообщение в чат.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если сообщение на момент пересылки будет удалено.
     * - Можно отправлять не более двух сообщений в секунду в один диалог, групповой чат или канал.
     *   При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед отправкой.
     *
     * @param int $chatId ID чата.
     * @see MaxApiClient::sendMessageToChat()
     */
    public function forwardToChat(int $chatId): SendMessageResult
    {
        $link = NewMessageLink::newFromMessage($this->getMessage(), MessageLinkType::Forward);
        $forwardMessage = new NewMessageBody(link: $link);

        return $this->apiClient->sendMessageToChat($chatId, $forwardMessage);
    }

    /**
     * Переслать сообщение в диалог с пользователем.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если сообщение на момент пересылки будет удалено.
     * - Чтобы бот мог отправить сообщение пользователю, пользователь должен запустить бота.
     *   Иначе запрос завершится ошибкой.
     * - Можно отправлять не более двух сообщений в секунду в один диалог. При превышении этого лимита
     *   сообщения следует ставить в очередь или делать задержку перед отправкой.
     *
     * @param int $userId ID пользователя.
     * @see MaxApiClient::sendMessageToUser()
     */
    public function forwardToUser(int $userId): SendMessageResult
    {
        $link = NewMessageLink::newFromMessage($this->getMessage(), MessageLinkType::Forward);
        $forwardMessage = new NewMessageBody(link: $link);

        return $this->apiClient->sendMessageToUser($userId, $forwardMessage);
    }

    /**
     * @return int ID диалога, чата или канала, где было отправлено сообщение.
     */
    public function getChatId(): int
    {
        return $this->getMessage()->getRecipient()->getChatId();
    }

    /**
     * Сообщение события.
     *
     * Если в событии нет сообщения, обработка события прерывается через {@see BaseEvent::continue()}.
     * Проверить наличие сообщения заранее можно методом {@see hasMessage()}.
     *
     * @return Message Сообщение события.
     */
    abstract public function getMessage(): Message;

    /**
     * @return User|null Пользователь, отправивший сообщение.
     *     Может быть `null`, если сообщение было опубликовано от имени канала.
     */
    public function getUser(): ?User
    {
        return $this->getMessage()->getSender();
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
     * Проверяет, что событие содержит сообщение.
     *
     * @return bool `true`, если событие содержит сообщение.
     */
    abstract public function hasMessage(): bool;

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
     * Проверяет, что тип чата сообщения не поддерживается.
     *
     * Возвращает `true`, если тип чата неизвестен, — то есть когда {@see isChannel()}, {@see isChat()}
     * и {@see isDialog()} возвращают `false`.
     *
     * @return bool `true`, если тип чата сообщения не поддерживается.
     */
    public function isUnsupported(): bool
    {
        return $this->getMessage()->getRecipient()->getChatType() === null;
    }

    /**
     * Ответить на сообщение в чате сообщения.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если сообщение на момент цитирования будет удалено.
     * - Можно отправлять не более двух сообщений в секунду в один диалог, групповой чат или канал.
     *   При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед отправкой.
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
        if ($asReply) {
            if (is_string($message)) {
                $message = new NewMessageBody($message);
            }
            $message->setReplyLink($this->getMessage());
        }

        return $this->sendMessageToChat($message, $disableLinkPreview);
    }

    /**
     * Ответить на сообщение лично в диалоге с пользователем.
     *
     * - Вы можете получить ошибку {@see NotFoundException}, если сообщение на момент пересылки будет удалено.
     * - Вы можете получить ошибку {@see SenderUnknownException}, если отправитель сообщения скрыт.
     * - Чтобы бот мог отправить сообщение пользователю, пользователь должен запустить бота.
     *   Иначе запрос завершится ошибкой.
     * - Можно отправлять не более двух сообщений в секунду в один диалог. При превышении этого лимита
     *   сообщения следует ставить в очередь или делать задержку перед отправкой.
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
        if ($forwardOrigMessage) {
            $link = NewMessageLink::newFromMessage($this->getMessage(), MessageLinkType::Forward);

            $this->sendMessageToUser(new NewMessageBody(link: $link));
        }

        return $this->sendMessageToUser($message, $disableLinkPreview);
    }

    /**
     * Пользователь, отправивший сообщение.
     *
     * В отличие от {@see getUser()}, вместо `null` выбрасывает исключение.
     *
     * @return User Пользователь, отправивший сообщение.
     * @throws SenderUnknownException Если сообщение опубликовано от имени канала.
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
     * ID пользователя, отправившего сообщение.
     *
     * В отличие от {@see getUserId()}, вместо `null` выбрасывает исключение.
     *
     * @return int ID пользователя, отправившего сообщение.
     * @throws SenderUnknownException Если сообщение опубликовано от имени канала.
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
