<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Кнопки в сообщении.
 */
class InlineKeyboardAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Keyboard|false $payload = false;

    public function getPayload(): Keyboard
    {
        return $this->payload === false
            ? $this->payload = Keyboard::newFromData($this->data['payload'])
            : $this->payload;
    }
}
