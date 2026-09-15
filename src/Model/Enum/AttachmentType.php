<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип вложения для сообщения.
 *
 * Кроме текста сообщения и посты могут содержать следующие типы вложений:
 * - `image` — изображение (JPG, JPEG, PNG, GIF, TIFF, BMP, HEIC).
 * - `video` — видео (MP4, MOV, MKV, WEBM, MATROSKA).
 * - `audio` — аудио (MP3, WAV, M4A и другие).
 * - `file` — файл (TXT, DOC и другие).
 * - `sticker` — стикер.
 * - `contact` — контакт (данные контакта из телефонного справочника).
 * - `inline_keyboard` — сообщение или пост с кнопкой.
 * - `share` — контент, прикреплённый по-внешнему URL.
 * - `location` — локация.
 */
enum AttachmentType: string
{
    use EnumHelperTrait;

    case Audio = 'audio';
    case Contact = 'contact';
    case Data = 'data';
    case File = 'file';
    case Image = 'image';
    case InlineKeyboard = 'inline_keyboard';
    case Location = 'location';
    case Share = 'share';
    case Sticker = 'sticker';
    case Video = 'video';
}
