<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Result of sending a message.
 *
 * @api
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
     * @return Message Info about created message.
     * @api
     */
    public function getMessage(): Message
    {
        return $this->message === false
            ? $this->message = Message::newFromData($this->data['message'])
            : $this->message;
    }
}
