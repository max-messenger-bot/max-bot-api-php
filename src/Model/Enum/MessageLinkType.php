<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип связанного сообщения или комментария.
 *
 * - `reply` — Ответ на сообщение или комментарий в чате или канале.
 * - `forward` — Пересланное сообщение в чате или канале.
 *
 * Для комментариев поддерживается только тип `reply`.
 * Сообщение из диалога или группового чата нельзя переслать в канал.
 *
 * @link https://dev.max.ru/docs-api/objects/Message
 * @link https://dev.max.ru/docs-api/objects/CommentMessage
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 * @link https://dev.max.ru/docs-api/objects/NewCommentBody
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 */
enum MessageLinkType: string
{
    case Forward = 'forward';
    case Reply = 'reply';
}
