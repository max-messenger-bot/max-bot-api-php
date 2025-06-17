<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Тип элемента разметки.
 *
 * Может быть \*\*жирный\*\*, \*курсив\*, \~зачеркнутый\~, \<ins>подчеркнутый\</ins>, \`моноширинный\`,
 * ссылка или упоминание пользователя.
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
