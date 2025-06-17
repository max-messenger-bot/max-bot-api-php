<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Enums;

enum TextFormat: string
{
    case Html = 'html';
    case Markdown = 'markdown';
}
