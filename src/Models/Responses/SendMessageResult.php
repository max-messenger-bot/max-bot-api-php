<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Информация о созданном сообщении.
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
