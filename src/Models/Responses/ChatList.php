<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Список чатов и указатель на следующую страницу данных.
 */
class ChatList extends BaseResponseModel
{
    /**
     * @var array{
     *     chats: list<array>,
     *     marker?: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<Chat>|false
     */
    private array|false $chats = false;

    /**
     * @return list<Chat> Список запрашиваемых чатов.
     */
    public function getChats(): array
    {
        return $this->chats === false
            ? ($this->chats = Chat::newListFromData($this->data['chats']))
            : $this->chats;
    }

    /**
     * @return int|null Указатель на следующую страницу запрашиваемых чатов.
     */
    public function getMarker(): ?int
    {
        return $this->data['marker'] ?? null;
    }
}
