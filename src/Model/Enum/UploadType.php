<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип загружаемого медиафайла.
 *
 * - `image` — Изображение (JPG, JPEG, PNG, GIF, TIFF, BMP, HEIC).
 * - `video` — Видео (MP4, MOV, MKV, WEBM, MATROSKA).
 * - `audio` — Аудио (MP3, WAV, M4A и другие).
 * - `file` — Файл (TXT, DOC, PDF и другие распространённые форматы).
 *
 * При передаче неподдерживаемого типа файла вернётся ошибка `File extension is forbidden`.
 *
 * Максимальный размер одного медиафайла, который можно загрузить, зависит от его типа:
 * - 50 МБ и не более 7680 × 7680 px — для изображений (должны выполняться оба критерия).
 * - 250 МБ — для видео.
 * - 256 МБ и длительностью не более 60 мин — для аудио (должны выполняться оба критерия).
 * - 4 ГБ — для файлов (`file`).
 *
 * @link https://dev.max.ru/docs-api/methods/POST/uploads/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/media
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/attachment-types
 */
enum UploadType: string
{
    case Audio = 'audio';
    case File = 'file';
    case Image = 'image';
    case Video = 'video';
}
