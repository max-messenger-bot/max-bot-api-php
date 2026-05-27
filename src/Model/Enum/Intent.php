<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Enum;

/**
 * Намерение кнопки.
 */
enum Intent: string
{
    case Default = 'default';
    case Negative = 'negative';
    case Positive = 'positive';
}
