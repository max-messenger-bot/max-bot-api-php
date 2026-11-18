<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Кнопки в сообщении.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
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
     * @return Keyboard Кнопки встроенной клавиатуры.
     */
    public function getPayload(): Keyboard
    {
        return $this->payload === false
            ? $this->payload = Keyboard::newFromData($this->data['payload'])
            : $this->payload;
    }
}
