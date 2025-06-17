<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Bot gets this type of update as soon as title has been changed in chat.
 *
 * @api
 */
class ChatTitleChangedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     title: non-empty-string,
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
     * @return non-empty-string New title.
     * @api
     */
    public function getTitle(): string
    {
        return $this->data['title'];
    }

    /**
     * @return User User who changed title.
     * @api
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }
}
