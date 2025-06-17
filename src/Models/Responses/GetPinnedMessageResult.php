<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class GetPinnedMessageResult extends BaseResponseModel
{
    /**
     * @var array{
     *     message?: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Message|false|null $message = false;

    /**
     * @return Message|null Закреплённое сообщение. Может быть `null`, если в чате нет закреплённого сообщения.
     */
    public function getMessage(): ?Message
    {
        return $this->message === false
            ? ($this->message = Message::newFromNullableData($this->data['message'] ?? null))
            : $this->message;
    }
}
