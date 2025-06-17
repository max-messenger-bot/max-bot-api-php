<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Paginated list of messages.
 *
 * @api
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
     * @return list<Message> List of messages.
     * @api
     */
    public function getMessages(): array
    {
        return $this->messages === false
            ? ($this->messages = Message::newListFromData($this->data['messages']))
            : $this->messages;
    }
}
