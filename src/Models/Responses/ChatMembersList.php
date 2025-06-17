<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Список участников и указатель на следующую страницу данных.
 */
class ChatMembersList extends BaseResponseModel
{
    /**
     * @var array{
     *     members: list<array>,
     *     marker?: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<ChatMember>|false
     */
    private array|false $members = false;

    /**
     * @return int|null Указатель на следующую страницу данных.
     */
    public function getMarker(): ?int
    {
        return $this->data['marker'] ?? null;
    }

    /**
     * @return list<ChatMember> Список участников чата с информацией о времени последней активности.
     */
    public function getMembers(): array
    {
        return $this->members === false
            ? ($this->members = ChatMember::newListFromData($this->data['members']))
            : $this->members;
    }
}
