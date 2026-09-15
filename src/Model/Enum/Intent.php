<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Намерение кнопки.
 *
 * @deprecated С 1 сентября 2026 г. перечисление удалено из официальной схемы API.
 *     Сервер принимает намерение кнопки, но игнорирует его и не возвращает в ответе.
 */
enum Intent: string
{
    case Default = 'default';
    case Negative = 'negative';
    case Positive = 'positive';
}
