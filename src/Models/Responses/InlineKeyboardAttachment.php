<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Inline keyboard attachment.
 *
 * Buttons in messages.
 *
 * @api
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

    /**
     * @return Keyboard Inline keyboard.
     * @api
     */
    public function getPayload(): Keyboard
    {
        return $this->payload === false
            ? $this->payload = Keyboard::newFromData($this->data['payload'])
            : $this->payload;
    }
}
