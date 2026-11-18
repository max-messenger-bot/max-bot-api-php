<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\ButtonType;

/**
 * Кнопка сообщения.
 *
 * При нажатии отправляет сообщение в чат от имени пользователя.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody/
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
final class MessageButton extends Button
{
    /**
     * @param non-empty-string|null $text Текст кнопки, который будет отправлен в чат от лица пользователя
     *     (maxLength: 128).
     */
    public function __construct(?string $text = null)
    {
        parent::__construct(ButtonType::Message, $text);
    }

    /**
     * @param non-empty-string $text Текст кнопки, который будет отправлен в чат от лица пользователя (maxLength: 128).
     */
    public static function make(string $text): self
    {
        return new self($text);
    }

    /**
     * @param non-empty-string|null $text Текст кнопки, который будет отправлен в чат от лица пользователя
     *     (maxLength: 128).
     */
    public static function new(?string $text = null): self
    {
        return new self($text);
    }
}
