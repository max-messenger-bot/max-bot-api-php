<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Events\Contracts;

use MaxMessenger\Api\Old\Events\Event;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

interface ListenerInterface
{
    public function check(Update $update): bool;

    public function handle(Event $event): void;
}
