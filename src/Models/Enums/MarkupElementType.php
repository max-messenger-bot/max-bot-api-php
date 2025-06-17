<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Type of the markup element.
 *
 * Can be \*\*strong\*\*, \*emphasized\*, \~strikethrough\~, \+\+underline\+\+, \`monospaced\`, link or user_mention.
 *
 * @api
 */
enum MarkupElementType: string
{
    case Emphasized = 'emphasized';
    case Link = 'link';
    case Monospaced = 'monospaced';
    case Strikethrough = 'strikethrough';
    case Strong = 'strong';
    case Underline = 'underline';
    case UserMention = 'user_mention';
}
