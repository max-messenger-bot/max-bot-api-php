<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Кнопка буфера обмена.
 *
 * Кнопка для копирования заданного текста в буфер обмена.
 */
final class ClipboardButton extends Button
{
    /**
     * @var array{
     *     payload: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Текст для буфера обмена (minLength: 1, maxLength: 1024).
     */
    public function getPayload(): string
    {
        return $this->data['payload'];
    }
}
