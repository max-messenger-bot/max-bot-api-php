<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Действие бота.
 *
 * - `mark_seen` — Бот помечает сообщения как прочитанные.
 * - `sending_audio` — Бот отправляет аудиофайл.
 * - `sending_file` — Бот отправляет файл.
 * - `sending_photo` — Бот отправляет фото.
 * - `sending_video` — Бот отправляет видео.
 * - `typing_on` — Бот набирает сообщение.
 *
 * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/actions/
 */
enum SenderAction: string
{
    case MarkSeen = 'mark_seen';
    case SendingAudio = 'sending_audio';
    case SendingFile = 'sending_file';
    case SendingPhoto = 'sending_photo';
    case SendingVideo = 'sending_video';
    case TypingOn = 'typing_on';
}
