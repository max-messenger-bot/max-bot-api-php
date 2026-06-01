<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

final class UnknownEvent extends BaseEvent
{
    public function getChatId(): null
    {
        return null;
    }

    public function getUser(): null
    {
        return null;
    }

    public function getUserId(): null
    {
        return null;
    }
}
