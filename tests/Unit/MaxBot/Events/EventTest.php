<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Events;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exceptions\MaxBot\Events\EventException;
use MaxMessenger\Bot\MaxBot\Events\Event;

final class EventTest extends Unit
{
    public function testBreak(): void
    {
        try {
            Event::break();
            self::fail('Expected exception was not thrown');
        } catch (EventException $e) {
            self::assertTrue($e->isHandled);
        }
    }

    public function testContinue(): void
    {
        try {
            Event::continue();
            self::fail('Expected exception was not thrown');
        } catch (EventException $e) {
            self::assertFalse($e->isHandled);
        }
    }

    public function testExit(): void
    {
        try {
            Event::exit();
            self::fail('Expected exception was not thrown');
        } catch (EventException $e) {
            self::assertNull($e->isHandled);
        }
    }
}
