<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Тип связанного сообщения.
 *
 * Возможные значения:
 * - `reply` — Ответ на сообщение или комментарий в чате или канале.
 * - `forward` — Пересланное сообщение в чате или канале.
 *
 * Для комментариев поддерживается только тип `reply`.
 */
enum MessageLinkType: string
{
    case Forward = 'forward';
    case Reply = 'reply';
}
