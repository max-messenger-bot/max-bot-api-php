<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * You will receive this update when bot has been removed from chat.
 *
 * @api
 */
class BotRemovedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     is_channel: bool,
     *     user: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int Chat identifier bot removed from.
     * @api
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return User User who removed bot from chat.
     * @api
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return bool Indicates whether bot has been removed from channel or not.
     * @api
     */
    public function isChannel(): bool
    {
        return $this->data['is_channel'];
    }
}
