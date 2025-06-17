<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Returns paginated response of chats.
 *
 * @api
 */
class ChatList extends BaseResponseModel
{
    /**
     * @var array{
     *     chats: list<array>,
     *     marker: int|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<Chat>|false
     */
    private array|false $chats = false;

    /**
     * @return list<Chat> List of requested chats.
     * @api
     */
    public function getChats(): array
    {
        return $this->chats === false
            ? ($this->chats = Chat::newListFromData($this->data['chats']))
            : $this->chats;
    }

    /**
     * @return int|null Reference to the next page of requested chats.
     * @api
     */
    public function getMarker(): ?int
    {
        return $this->data['marker'];
    }
}
