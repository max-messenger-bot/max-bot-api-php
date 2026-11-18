<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип кнопки.
 *
 * - `callback` — Отправляет боту событие `message_callback` с данными кнопки.
 * - `clipboard` — Копирует текст из `payload` в буфер обмена.
 * - `link` — Открывает ссылку.
 * - `message` — Отправляет в чат сообщение от имени пользователя с текстом кнопки.
 * - `open_app` — Открывает мини-приложение.
 * - `request_contact` — Запрашивает контакт и номер телефона пользователя, привязанный к профилю МАКС.
 * - `request_geo_location` — Запрашивает местоположение пользователя.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 * @link https://dev.max.ru/docs-api/objects/Message
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 */
enum ButtonType: string
{
    case Callback = 'callback';
    case Clipboard = 'clipboard';
    case Link = 'link';
    case Message = 'message';
    case OpenApp = 'open_app';
    case RequestContact = 'request_contact';
    case RequestGeoLocation = 'request_geo_location';
}
