<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Pinned message result.
 *
 * @api
 */
class GetPinnedMessageResult extends BaseResponseModel
{
    /**
     * @var array{
     *     message?: array|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Message|false|null $message = false;

    /**
     * Get pinned message.
     *
     * @return Message|null Pinned message. Can be `null` if no message pinned in chat.
     * @api
     */
    public function getMessage(): ?Message
    {
        return $this->message === false
            ? ($this->message = Message::newFromNullableData($this->data['message'] ?? null))
            : $this->message;
    }
}
