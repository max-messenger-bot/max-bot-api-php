<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Returns members list and pointer to the next data page.
 *
 * @api
 */
class ChatMembersList extends BaseResponseModel
{
    /**
     * @var array{
     *     marker?: int|null,
     *     members: list<array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<ChatMember>|false
     */
    private array|false $members = false;

    /**
     * @return int|null Pointer to the next data page.
     * @api
     */
    public function getMarker(): ?int
    {
        return $this->data['marker'] ?? null;
    }

    /**
     * @return list<ChatMember> Participants in chat with time of last activity.
     *     Visible only for chat admins.
     * @api
     */
    public function getMembers(): array
    {
        return $this->members === false
            ? ($this->members = ChatMember::newListFromData($this->data['members']))
            : $this->members;
    }
}
