<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Inline keyboard attachment.
 *
 * Buttons in messages.
 *
 * @api
 */
readonly class InlineKeyboardAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return Keyboard Inline keyboard.
     * @api
     */
    public function getPayload(): Keyboard
    {
        return Keyboard::newFromData($this->data['payload']);
    }
}
