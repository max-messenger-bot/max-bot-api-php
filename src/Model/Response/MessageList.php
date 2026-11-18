<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\MaxApiClient;

/**
 * Массив сообщений.
 *
 * Маркер следующей страницы API не возвращает: для перебора сдвигайте границы промежутка времени
 * `before` и `after` в {@see MaxApiClient::getMessages()}.
 *
 * @link https://dev.max.ru/docs-api/methods/GET/messages/
 */
class MessageList extends BaseResponseModel
{
    /**
     * @var array{
     *     messages: list<array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<Message>|false
     */
    private array|false $messages = false;

    /**
     * @return list<Message> Массив сообщений.
     */
    public function getMessages(): array
    {
        return $this->messages === false
            ? ($this->messages = Message::newListFromData($this->data['messages']))
            : $this->messages;
    }
}
