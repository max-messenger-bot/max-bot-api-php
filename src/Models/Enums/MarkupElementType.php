<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Тип элемента разметки.
 *
 * Может быть \*\*жирный\*\*, \*курсив\*, \~зачёркнутый\~, \<ins>подчёркнутый\</ins>, \`моноширинный\`, выделенный,
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
