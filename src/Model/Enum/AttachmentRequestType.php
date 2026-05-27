<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип вложения для сообщения.
 */
enum AttachmentRequestType: string
{
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
