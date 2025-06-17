<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Тип загружаемого файла.
 *
 * Поддерживаемые форматы:
 * - `image`: JPG, JPEG, PNG, GIF, TIFF, BMP, HEIC.
 * - `video`: MP4, MOV, MKV, WEBM, MATROSKA.
 * - `audio`: MP3, WAV, M4A и другие.
 * - `file`: любые типы файлов.
 */
enum UploadType: string
{
    case Audio = 'audio';
    case File = 'file';
    case Image = 'image';
    case Video = 'video';
}
