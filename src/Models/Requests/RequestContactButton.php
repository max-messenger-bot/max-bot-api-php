<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ButtonType;

/**
 * Кнопка запроса контакта.
 *
 * После нажатия на такую кнопку клиент отправляет новое сообщение с вложением текущего контакта пользователя.
 */
final class RequestContactButton extends Button
{
    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     */
    public function __construct(?string $text = null)
    {
        $this->required = ['text'];

        parent::__construct(ButtonType::RequestContact, $text);
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     */
    public static function make(string $text): self
    {
        return new self($text);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     */
    public static function new(?string $text = null): self
    {
        return new self($text);
    }
}
