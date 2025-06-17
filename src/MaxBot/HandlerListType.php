<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot;

enum HandlerListType
{
    case Prepare;
    case Event;
    case Typed;
    case Fallback;
    case Final;
}
