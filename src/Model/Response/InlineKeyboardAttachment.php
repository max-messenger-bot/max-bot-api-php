<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Кнопки в сообщении.
 *
 * Подробнее о типах кнопок и клавиатуре в ботах —
 * {@link https://dev.max.ru/docs-api#Клавиатура%20для%20чат-бота в разделе «Клавиатура для чат-бота»}.
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
