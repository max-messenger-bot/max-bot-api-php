<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип загружаемого медиафайла.
 *
 * Возможные значения и поддерживаемые форматы:
 * - `image` — изображение (JPG, JPEG, PNG, GIF, TIFF, BMP, HEIC).
 * - `video` — видеофайл (MP4, MOV, MKV, WEBM, MATROSKA).
 * - `audio` — аудиофайл (MP3, WAV, M4A и другие).
 * - `file` — файл, поддерживаются распространённые форматы (например, TXT, DOC и другие).
 *
 * При передаче неподдерживаемого типа файла вернётся ошибка `File extension is forbidden`.
 *
 * Максимальный размер одного файла, который можно загрузить, зависит от его типа:
 * - 250 МБ — для видео.
 * - 4 ГБ — для файлов.
 */
enum UploadType: string
{
    case Audio = 'audio';
    case File = 'file';
    case Image = 'image';
    case Video = 'video';
}
