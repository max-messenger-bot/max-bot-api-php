<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxBot\Event\ChatIdMissingException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\SendMessageResult;

/**
 * Методы отправки сообщения и действия бота в чат, к которому относится событие.
 *
 * @psalm-require-extends BaseEvent
 * @property-read MaxApiClient $apiClient
 */
trait SendMessageToChatTrait
{
    abstract public function getChatId(): int;

    abstract public function requireChatId(): int;

    /**
     * Отправить действие бота в чат события.
     *
     * @param SenderAction $action Действие бота.
     * @return bool Всегда `true`.
     * @deprecated Метод будет удалён в следующих версиях. Используйте {@see sendActionToChat()}.
     */
    public function sendAction(SenderAction $action): bool
    {
        return $this->sendActionToChat($action);
    }

    /**
     * Отправить действие бота в чат события.
     *
     * Отправляет такие действия бота, как «набор текста» или «отправка фото», а также отметку о прочтении сообщений.
     *
     * - Действие отображается в диалогах и групповых чатах. Для каналов сервер принимает запрос,
     *   но участникам действие не показывается.
     *
     * @param SenderAction $action Действие бота.
     * @return bool Всегда `true`. Deprecated: тип возвращаемого значения устарел — в следующих версиях
     *     он будет заменён на `void`.
     * @see MaxApiClient::sendAction()
     */
    public function sendActionToChat(SenderAction $action): bool
    {
        $this->apiClient->sendAction($this->getChatId(), $action);

        return true;
    }

    /**
     * Отправить сообщение в чат события.
     *
     * - Вы получите ошибку {@see ChatIdMissingException}, если у события нет чата.
     * - Можно отправлять не более двух сообщений в секунду в один диалог, групповой чат или канал.
     *   При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед отправкой.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @see MaxApiClient::sendMessageToChat()
     */
    public function sendMessageToChat(
        NewMessageBody|string $message,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->apiClient->sendMessageToChat($this->requireChatId(), $message, $disableLinkPreview);
    }

    /**
     * Отправить сообщение в чат события.
     *
     * @param NewMessageBody|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @deprecated Метод будет удалён в следующих версиях. Используйте {@see sendMessageToChat()}.
     */
    public function sendToChat(NewMessageBody|string $message, bool $disableLinkPreview = false): SendMessageResult
    {
        return $this->sendMessageToChat($message, $disableLinkPreview);
    }
}
