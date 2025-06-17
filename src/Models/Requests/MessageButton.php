<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ButtonType;

/**
 * Кнопка сообщения.
 *
 * Кнопка для отправки сообщения.
 */
final class MessageButton extends Button
{
    /**
     * @param non-empty-string|null $text Текст кнопки, который будет отправлен в чат от лица пользователя
     *     (minLength: 1, maxLength: 128).
     */
    public function __construct(?string $text = null)
    {
        $this->required = ['text'];

        parent::__construct(ButtonType::Message, $text);
    }

    /**
     * @param non-empty-string $text Текст кнопки, который будет отправлен в чат от лица пользователя
     *     (minLength: 1, maxLength: 128).
     */
    public static function make(string $text): self
    {
        return new self($text);
    }

    /**
     * @param non-empty-string|null $text Текст кнопки, который будет отправлен в чат от лица пользователя
     *     (minLength: 1, maxLength: 128).
     */
    public static function new(?string $text = null): self
    {
        return new self($text);
    }
}
