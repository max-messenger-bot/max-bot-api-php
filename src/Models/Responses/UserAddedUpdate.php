<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * You will receive this update when user has been added to chat where bot is administrator.
 *
 * @api
 */
class UserAddedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     inviter_id?: int|null,
     *     is_channel: bool,
     *     user: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int Chat identifier where event has occurred.
     * @api
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return int|null User who added user to chat.
     *     Can be `null` in case when user joined chat by link.
     * @api
     */
    public function getInviterId(): ?int
    {
        return $this->data['inviter_id'] ?? null;
    }

    /**
     * @return User User added to chat.
     * @api
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return bool Indicates whether user has been added to channel or not.
     * @api
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
