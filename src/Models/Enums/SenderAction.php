<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Действие, отправляемое участникам чата.
 *
 * Возможные значения:
 * - `typing_on` — Бот набирает сообщение.
 * - `sending_photo` — Бот отправляет фото.
 * - `sending_video` — Бот отправляет видео.
 * - `sending_audio` — Бот отправляет аудиофайл.
 * - `sending_file` — Бот отправляет файл.
 * - `mark_seen` — Бот помечает сообщения как прочитанные.
 */
enum SenderAction: string
{
    case TypingOn = 'typing_on';
    case SendingPhoto = 'sending_photo';
    case SendingVideo = 'sending_video';
    case SendingAudio = 'sending_audio';
    case SendingFile = 'sending_file';
    case MarkSeen = 'mark_seen';
}
