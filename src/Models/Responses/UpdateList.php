<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * List of all updates in chats your bot participated in.
 *
 * @api
 */
class UpdateList extends BaseResponseModel
{
    /**
     * @var array{
     *     updates: list<array>,
     *     marker: int|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<Update>|false
     */
    private array|false $updates = false;

    /**
     * @return int|null Pointer to the next data page.
     * @api
     */
    public function getMarker(): ?int
    {
        return $this->data['marker'];
    }

    /**
     * @return list<Update> Page of updates.
     * @api
     */
    public function getUpdates(): array
    {
        return $this->updates === false
            ? ($this->updates = Update::newListFromData($this->data['updates']))
            : $this->updates;
    }
}
