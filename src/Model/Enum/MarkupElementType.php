<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип элемента разметки.
 *
 * Может быть \*\*жирный\*\*, \*курсив\*, ~~зачёркнутый~~, ++подчёркнутый++, \`моноширинный\`, ^^выделенный^^,
 * цитата, заголовок, ссылка или упоминание пользователя.
 */
enum MarkupElementType: string
{
    case Emphasized = 'emphasized';
    case Heading = 'heading';
    case Highlighted = 'highlighted';
    case Link = 'link';
    case Monospaced = 'monospaced';
    case Quote = 'quote';
    case Strikethrough = 'strikethrough';
    case Strong = 'strong';
    case Underline = 'underline';
    case UserMention = 'user_mention';
}
