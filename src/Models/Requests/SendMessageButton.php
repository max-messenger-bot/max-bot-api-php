<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ReplyButtonType;

/**
 * Кнопка отправки сообщения.
 *
 * После нажатия на такую кнопку, клиент отправляет сообщение с заданным payload.
 *
 * @deprecated В текущей версии API не используется.
 */
final class SendMessageButton extends ReplyButton
{
    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $payload Токен кнопки.
     */
    public function __construct(?string $text = null, ?string $payload = null)
    {
        $this->required = ['text'];

        parent::__construct(ReplyButtonType::Message, $text, $payload);
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $payload Токен кнопки.
     */
    public static function make(string $text, ?string $payload = null): self
    {
        return new self($text, $payload);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $payload Токен кнопки.
     */
    public static function new(?string $text = null, ?string $payload = null): self
    {
        return new self($text, $payload);
    }
}
