<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Events;

use MaxMessenger\Api\MaxBot;
use MaxMessenger\Api\Old\Events\Exceptions\EventExitException;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

final readonly class Event
{
    public function __construct(
        public MaxBot $bot,
        public Update $update
    ) {
    }

    /**
     * @throws EventExitException
     */
    public function break(): void
    {
        throw new EventExitException(false);
    }

    /**
     * @throws EventExitException
     */
    public function continue(): never
    {
        throw new EventExitException(true);
    }
}
