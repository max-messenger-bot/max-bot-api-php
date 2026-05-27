<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Формат текста сообщения.
 */
enum TextFormat: string
{
    case Html = 'html';
    case Markdown = 'markdown';
}
