<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Enums;

/**
 * Intent of button.
 *
 * @api
 */
enum Intent: string
{
    case Default = 'default';
    case Negative = 'negative';
    case Positive = 'positive';
}
