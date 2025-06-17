<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * You will get this `update` as soon as message is removed.
 *
 * @api
 */
class MessageRemovedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     message_id: non-empty-string,
     *     user_id: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Chat identifier where message has been deleted.
     * @api
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return non-empty-string Identifier of removed message.
     * @api
     */
    public function getMessageId(): string
    {
        return $this->data['message_id'];
    }

    /**
     * @return int User who deleted this message.
     * @api
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }
}
