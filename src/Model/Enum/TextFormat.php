<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Формат текста сообщения.
 *
 * В тексте комментариев не поддерживаются гиперссылки и упоминание пользователей.
 */
enum TextFormat: string
{
    case Html = 'html';
    case Markdown = 'markdown';
}
