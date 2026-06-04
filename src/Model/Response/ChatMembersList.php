<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Список участников группового чата или канала и указатель на следующую страницу данных.
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
     * @return list<ChatMember> Список участников группового чата или канала с общей информацией о них, а также
     *     временем последней активности и списком прав доступа для пользователей и ботов,
     *     которые являются администраторами.
     */
    public function getMembers(): array
    {
        return $this->members === false
            ? ($this->members = ChatMember::newListFromData($this->data['members']))
            : $this->members;
    }
}
