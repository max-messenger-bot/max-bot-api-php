<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип вложения.
 *
 * - `image` — Изображение (JPG, JPEG, PNG, GIF, TIFF, BMP, HEIC).
 * - `video` — Видео (MP4, MOV, MKV, WEBM, MATROSKA).
 * - `audio` — Аудио (MP3, M4A и другие).
 * - `file` — Файл (TXT, DOC, PDF и другие).
 * - `sticker` — Стикер.
 * - `contact` — Контактные данные.
 * - `inline_keyboard` — Клавиатура с кнопками.
 * - `share` — Предпросмотр контента по внешнему URL.
 * - `location` — Геолокация.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/attachment-types
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 * @link https://dev.max.ru/docs-api/objects/Message
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 */
enum AttachmentType: string
{
    use EnumHelperTrait;

    case Audio = 'audio';
    case Contact = 'contact';
    case File = 'file';
    case Image = 'image';
    case InlineKeyboard = 'inline_keyboard';
    case Location = 'location';
    case Share = 'share';
    case Sticker = 'sticker';
    case Video = 'video';
}
