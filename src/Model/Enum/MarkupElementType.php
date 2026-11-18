<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип элемента разметки.
 *
 * - `emphasized` — Курсив.
 * - `heading` — Заголовок.
 * - `highlighted` — Выделенный текст.
 * - `link` — Гиперссылка.
 * - `monospaced` — Моноширинный текст или блок кода.
 * - `quote` — Цитата.
 * - `strikethrough` — Зачёркнутый текст.
 * - `strong` — Жирный текст.
 * - `underline` — Подчёркнутый текст.
 * - `user_mention` — Упоминание пользователя по публичному имени или идентификатору.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/text-formatting
 * @link https://dev.max.ru/docs-api/objects/Message
 * @link https://dev.max.ru/docs-api/objects/CommentMessage
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
