<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Информация о созданном сообщении.
 *
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 */
class SendMessageResult extends BaseResponseModel
{
    /**
     * @var array{
     *     message: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Message|false $message = false;

    /**
     * @return Message Информация о созданном сообщении.
     */
    public function getMessage(): Message
    {
        return $this->message === false
            ? $this->message = Message::newFromData($this->data['message'])
            : $this->message;
    }
}
