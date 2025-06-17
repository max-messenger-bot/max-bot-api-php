<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * You will receive this update when user has been removed from chat where bot is administrator.
 *
 * @api
 */
class UserRemovedUpdate extends Update
{
    /**
     * @var array{
     *     admin_id?: int|null,
     *     chat_id: int,
     *     is_channel: bool,
     *     user: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int|null Administrator who removed user from chat.
     *     Can be `null` in case when user left chat.
     * @api
     */
    public function getAdminId(): ?int
    {
        return $this->data['admin_id'] ?? null;
    }

    /**
     * @return int Chat identifier where event has occurred.
     * @api
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return User User removed from chat.
     * @api
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return bool Indicates whether user has been removed from channel or not.
     * @api
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
