<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Кнопка буфера обмена.
 *
 * После нажатия на кнопку указанный текст копируется в буфер обмена.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
class ClipboardButton extends Button
{
    /**
     * @var array{
     *     payload: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Текст, который копируется в буфер обмена после нажатия на кнопку (maxLength: 1024).
     */
    public function getPayload(): string
    {
        return $this->data['payload'];
    }
}
