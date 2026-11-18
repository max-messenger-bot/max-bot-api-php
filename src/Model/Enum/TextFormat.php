<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Формат текста.
 *
 * - `html` — Поддерживаемое подмножество HTML.
 * - `markdown` — Поддерживаемое подмножество Markdown.
 *
 * При формате `html` неподдерживаемые теги удаляются.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/text-formatting
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 * @link https://dev.max.ru/docs-api/objects/NewCommentBody
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 */
enum TextFormat: string
{
    case Html = 'html';
    case Markdown = 'markdown';
}
