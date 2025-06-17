<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use MaxMessenger\Bot\Models\Enums\ChatType;

/**
 * New message recipient. Could be user or chat.
 *
 * @api
 */
readonly class Recipient extends BaseResponseModel
{
    /**
     * @var array{
     *     chat_id: int|null,
     *     chat_type: string,
     *     user_id: int|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return int|null Chat identifier.
     * @api
     */
    public function getChatId(): ?int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return ChatType Chat type.
     * @api
     */
    public function getChatType(): ChatType
    {
        return ChatType::from($this->data['chat_type']);
    }

    /**
     * @return string Chat type.
     * @api
     */
    public function getChatTypeRaw(): string
    {
        return $this->data['chat_type'];
    }

    /**
     * @return int|null User identifier, if message was sent to user.
     * @api
     */
    public function getUserId(): ?int
    {
        return $this->data['user_id'];
    }
}
