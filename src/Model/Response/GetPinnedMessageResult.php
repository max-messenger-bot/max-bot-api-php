<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Результат запроса закреплённого сообщения чата или канала.
 *
 * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/pin/
 */
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
     * @return Message|null Закреплённое сообщение. Может быть `null`, если в чате или канале нет закрепления.
     */
    public function getMessage(): ?Message
    {
        return $this->message === false
            ? ($this->message = Message::newFromNullableData($this->data['message'] ?? null))
            : $this->message;
    }
}
