<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * You will get this `update` as soon as message is edited.
 *
 * @api
 */
class MessageEditedUpdate extends Update
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
     * @return Message Edited message.
     * @api
     */
    public function getMessage(): Message
    {
        return $this->message === false
            ? $this->message = Message::newFromData($this->data['message'])
            : $this->message;
    }
}
