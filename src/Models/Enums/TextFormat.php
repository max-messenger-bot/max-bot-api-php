<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Message text format.
 *
 * @api
 */
enum TextFormat: string
{
    case Html = 'html';
    case Markdown = 'markdown';
}
